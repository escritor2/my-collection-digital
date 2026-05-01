import { useRuntimeConfig } from '#app';
import { enqueueJob, readQueue, removeJob } from '~/composables/useOfflineQueue';

let handlingUnauthorized = false;

export const useApi = () => {
    const config = useRuntimeConfig();
    const configuredBaseUrl = ((config.public.apiBaseUrl as string) || '').trim().replace(/\/$/, '');
    const inferredBaseUrl = import.meta.client
        ? `${window.location.protocol}//${window.location.hostname}:8000`
        : 'http://127.0.0.1:8000';
    const baseURL = (configuredBaseUrl || inferredBaseUrl).replace(/\/$/, '');
    const apiBaseURL = `${baseURL}/api`;

    const fetchCsrfCookie = async () => {
        await $fetch('/sanctum/csrf-cookie', {
            baseURL,
            method: 'GET',
            credentials: 'include' as RequestCredentials,
        });
    };

    const getXsrfToken = () => {
        if (import.meta.server) return null;
        const name = 'XSRF-TOKEN=';
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
        }
        return null;
    };

    const apiFetch = async (endpoint: string, options: any = {}) => {
        // If offline, queue certain mutation requests to sync later.
        const method = String(options?.method || 'GET').toUpperCase();
        const isMutation = method !== 'GET' && method !== 'HEAD';
        const isOffline = typeof navigator !== 'undefined' ? navigator.onLine === false : false;
        const canQueue =
            endpoint.startsWith('/reader/') ||
            endpoint.startsWith('/user-shelf') ||
            endpoint.startsWith('/analytics') === false; // just avoid useless queueing

        if (isMutation && isOffline && canQueue) {
            await enqueueJob({
                method: method as any,
                url: endpoint,
                body: options?.body,
            });
            return { queued: true };
        }

        const xsrfToken = getXsrfToken();
        const headers: any = {
            'Accept': 'application/json',
            ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            ...options.headers,
        };

        if (import.meta.server) {
            const { cookie } = useRequestHeaders(['cookie']);
            if (cookie) {
                headers.cookie = cookie;
            }
            // Força o referer para o backend aceitar o CORS/Sanctum stateful
            headers.referer = baseURL;
        }

        const fetchOptions = {
            baseURL: apiBaseURL,
            credentials: 'include' as RequestCredentials,
            ...options,
            headers,
        };

        try {
            return await $fetch(endpoint, fetchOptions);
        } catch (error: any) {
            // CSRF token mismatch error usually returns 419 in Laravel
            if (error.response?.status === 419) {
                await fetchCsrfCookie();
                // Update headers with new token
                const newToken = getXsrfToken();
                if (newToken) {
                    fetchOptions.headers = {
                        ...fetchOptions.headers,
                        'X-XSRF-TOKEN': newToken
                    };
                }
                return await $fetch(endpoint, fetchOptions);
            }
            
            // Redirect to login if unauthenticated (avoid logout loops).
            if (error.response?.status === 401) {
                const { clearAuthState } = useAuth();
                clearAuthState();

                if (!handlingUnauthorized) {
                    handlingUnauthorized = true;
                    navigateTo('/login').finally(() => {
                        handlingUnauthorized = false;
                    });
                }
            }

            throw error;
        }
    };

    const flushOfflineQueue = async () => {
        const isOffline = typeof navigator !== 'undefined' ? navigator.onLine === false : false;
        if (isOffline) return { flushed: 0 };

        const jobs = await readQueue();
        let flushed = 0;
        for (const j of jobs) {
            try {
                await $fetch(j.url, {
                    baseURL: apiBaseURL,
                    method: j.method,
                    body: j.body,
                    credentials: 'include' as RequestCredentials,
                    headers: {
                        'Accept': 'application/json',
                        ...(getXsrfToken() ? { 'X-XSRF-TOKEN': getXsrfToken() } : {}),
                    },
                });
                await removeJob(j.id);
                flushed++;
            } catch {
                // stop on first failure (likely auth/CSRF). We'll retry later.
                break;
            }
        }
        return { flushed };
    };

    return {
        apiFetch,
        fetchCsrfCookie,
        getXsrfToken,
        baseURL,
        apiBaseURL,
        flushOfflineQueue,
    };
};
