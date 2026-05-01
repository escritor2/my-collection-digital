import { ref } from 'vue';

export const useAuth = () => {
    const user = useState<any>('auth-user', () => null);
    const isAuthenticated = useState<boolean>('auth-authenticated', () => false);

    const clearAuthState = () => {
        user.value = null;
        isAuthenticated.value = false;
    };

    const login = async (credentials: any) => {
        const { apiFetch, fetchCsrfCookie, getXsrfToken, baseURL } = useApi();
        
        // 1. Get CSRF Cookie first
        await fetchCsrfCookie();
        
        const xsrfToken = getXsrfToken();

        // 2. Perform Login (hitting Laravel's default Fortify/Sanctum login route)
        // Note: Login route is outside of /api prefix in standard Laravel
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

    const logout = async () => {
        const { apiFetch, getXsrfToken, baseURL } = useApi();
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
            clearAuthState();
        }
    };

    const fetchUser = async () => {
        const { apiFetch } = useApi();
        try {
            const response = await apiFetch('/user');
            user.value = response;
            isAuthenticated.value = true;
        } catch (e) {
            clearAuthState();
        }
    };

    return {
        user,
        isAuthenticated,
        login,
        logout,
        fetchUser,
        clearAuthState,
    };
};

