import { useAuth } from '~/composables/useAuth';

export default defineNuxtRouteMiddleware((to, from) => {
    const { isAuthenticated } = useAuth();
    
    // Allow login and register routes for unauthenticated users
    if (to.path === '/login' || to.path === '/register') {
        if (isAuthenticated.value) {
            return navigateTo('/dashboard');
        }
        return;
    }

    // Require authentication for other routes like dashboard
    if (!isAuthenticated.value) {
        return navigateTo('/login');
    }
});