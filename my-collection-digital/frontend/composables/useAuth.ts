import { ref } from 'vue';

export const useAuth = () => {
    const user = ref<any>(null);
    const isAuthenticated = ref(false);

    const login = async (credentials: any) => {
        const { apiFetch, fetchCsrfCookie, getXsrfToken } = useApi();
        
        // 1. Get CSRF Cookie first
        await fetchCsrfCookie();
        
        const xsrfToken = getXsrfToken();

        // 2. Perform Login (hitting Laravel's default Fortify/Sanctum login route)
        // Note: Login route is outside of /api prefix in standard Laravel
        await $fetch('http://127.0.0.1:8000/login', {
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
        const { apiFetch, getXsrfToken } = useApi();
        const xsrfToken = getXsrfToken();
        try {
            await $fetch('http://127.0.0.1:8000/logout', {
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
        logout,
        fetchUser
    };
};

