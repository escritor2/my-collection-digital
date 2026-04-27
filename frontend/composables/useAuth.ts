import { useState } from '#app';
import { useApi } from '~/composables/useApi';

export const useAuth = () => {
    const user = useState<any>('auth_user', () => null);
    const isAuthenticated = useState<boolean>('auth_authenticated', () => false);

    const login = async (credentials: any) => {
        const { fetchCsrfCookie, getXsrfToken, baseURL } = useApi();
        
        // 1. Get CSRF Cookie first
        await fetchCsrfCookie();
        
        const xsrfToken = getXsrfToken();

        // 2. Perform Login (hitting Laravel's default Fortify/Sanctum login route)
        await $fetch(`${baseURL}/login`, {
            method: 'POST',
            body: credentials,
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            }
        });

        // 3. Fetch User profile
        await fetchUser();
    };

    const register = async (data: any) => {
        const { fetchCsrfCookie, getXsrfToken, baseURL } = useApi();
        
        await fetchCsrfCookie();
        
        const xsrfToken = getXsrfToken();

        await $fetch(`${baseURL}/register`, {
            method: 'POST',
            body: data,
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            }
        });

        await fetchUser();
    };

    const logout = async () => {
        const { getXsrfToken, baseURL } = useApi();
        const xsrfToken = getXsrfToken();
        try {
            await $fetch(`${baseURL}/logout`, {
                method: 'POST',
                credentials: 'include',
                headers: { 
                    'Accept': 'application/json',
                    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
                }
            });
        } catch (e) {
            console.error('Logout error', e);
        } finally {
            user.value = null;
            isAuthenticated.value = false;
        }
    };

    const fetchUser = async () => {
        const { apiFetch } = useApi();
        try {
            const response = await apiFetch('/user');
            user.value = response;
            isAuthenticated.value = true;
        } catch (e) {
            user.value = null;
            isAuthenticated.value = false;
        }
    };

    return {
        user,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser
    };
};


