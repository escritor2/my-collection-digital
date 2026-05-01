export default defineNuxtRouteMiddleware(async () => {
  const { fetchUser, isAuthenticated } = useAuth();
  if (!isAuthenticated.value) {
    await fetchUser();
  }

  if (!isAuthenticated.value) {
    return navigateTo('/login');
  }
});

