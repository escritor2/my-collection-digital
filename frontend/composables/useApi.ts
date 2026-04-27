import { useRuntimeConfig, navigateTo } from '#app';

export const useApi = () => {
    const config = useRuntimeConfig();
    const baseURL = config.public.apiBase;

    const fetchCsrfCookie = async () => {
        await $fetch('/sanctum/csrf-cookie', {
            baseURL,
            method: 'GET',
        });
    };

    const getXsrfToken = () => {
        if (process.server) return null;
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
        const xsrfToken = getXsrfToken();
        const fetchOptions = {
            baseURL: `${baseURL}/api`,
            credentials: 'include' as RequestCredentials,
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
                ...options.headers,
            },
        };

        try {
            return await $fetch(endpoint, fetchOptions);
        } catch (error: any) {
            // CSRF token mismatch error usually returns 419 in Laravel
            if (error.response?.status === 419) {
                await fetchCsrfCookie();
                return await $fetch(endpoint, fetchOptions);
            }

            // Redirect to login if unauthenticated
            if (error.response?.status === 401 && process.client) {
                navigateTo('/login');
            }

            throw error;
        }
    };

    return {
        apiFetch,
        fetchCsrfCookie,
        getXsrfToken,
        baseURL
    };
};
