import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import type { Role } from '@/types';

declare module 'vue-router' {
  interface RouteMeta {
    hideNavbar?: boolean;
    requiresAuth?: boolean;
    roles?: Role[];
  }
}

// Layouts / Pages — lazy loaded
const AdminLayout = () => import('../admin/layouts/AdminLayout.vue');
const AdminHome = () => import('../admin/pages/AdminHome.vue');
const AdminUsers = () => import('../admin/pages/AdminUsers.vue');
const AdminMarket = () => import('../admin/pages/AdminMarket.vue');
const AdminProfile = () => import('../admin/pages/AdminProfile.vue');

const LandingPage = () => import('../pages/LandingPage.vue');
const UserDashboard = () => import('../pages/UserDashboard.vue');
const UserProfile = () => import('../pages/UserProfile.vue');
const TradePage = () => import('../pages/TradePage.vue');
const SignupPage = () => import('../pages/SignupPage.vue');
const SigninPage = () => import('../pages/SigninPage.vue');
const ChangePasswordPage = () => import('../pages/ChangePassword.vue');
const SupportPage = () => import('../pages/SupportPage.vue');
const Portfolio = () => import('../pages/Portfolio.vue');

const routes = [
  // Admin routes: hide Navbar via meta
  {
    path: '/admin',
    component: AdminLayout,
    meta: { hideNavbar: true, requiresAuth: true, roles: ['admin'] },
    children: [
      { path: '', name: 'AdminHome', component: AdminHome },
      { path: 'users', name: 'AdminUsers', component: AdminUsers },
      { path: 'market', name: 'AdminMarket', component: AdminMarket },
      { path: 'profile', name: 'AdminProfile', component: AdminProfile }
    ]
  },

  // Public routes that show Navbar (default)
  { path: '/', name: 'Landing', component: LandingPage, meta: { hideNavbar: true } },
  { path: '/signin', name: 'Signin', component: SigninPage, meta: { hideNavbar: true } },
  { path: '/signup', name: 'Signup', component: SignupPage, meta: { hideNavbar: true } },
  { path: '/change-password', name: 'ChangePassword', component: ChangePasswordPage, meta: { requiresAuth: true, hideNavbar: true } },
  { path: '/dashboard', name: 'Dashboard', component: UserDashboard, meta: { requiresAuth: true, roles: ['client', 'admin'] } },
  { path: '/profile', name: 'Profile', component: UserProfile, meta: { requiresAuth: true, roles: ['client', 'admin'] } },
  { path: '/trade', name: 'Trade', component: TradePage, meta: { requiresAuth: true, roles: ['client', 'admin'] } },
  { path: '/support', name: 'Support', component: SupportPage, meta: { requiresAuth: true, roles: ['client', 'admin'] } },
  { path: '/app/portfolio', name: 'Portfolio', component: Portfolio, meta: { requiresAuth: true, roles: ['client'] } },

  // Fallback
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore();
  auth.hydrate();

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
  if (!requiresAuth) {
    if (to.name === 'Signin' && auth.isAuthenticated) {
      return next(auth.user?.role === 'admin' ? '/admin' : '/dashboard');
    }
    return next();
  }

  if (!auth.token) {
    return next({ name: 'Signin', query: { redirect: to.fullPath } });
  }

  if (!auth.user) {
    await auth.fetchCurrentUser();
  }

  if (!auth.user) {
    return next({ name: 'Signin', query: { redirect: to.fullPath } });
  }

  const status = auth.user.status;
  const mustChangePassword = auth.needsPasswordChange;

  if (mustChangePassword && to.name !== 'ChangePassword') {
    return next({ name: 'ChangePassword' });
  }

  if (!mustChangePassword && status !== 'active' && to.name !== 'ChangePassword') {
    return next({ name: 'Signin', query: { reason: status } });
  }

  const allowedRoles = to.matched.flatMap((record) => record.meta.roles ?? []);
  if (allowedRoles.length && !allowedRoles.includes(auth.user.role)) {
    return next(auth.user.role === 'admin' ? '/admin' : '/');
  }

  return next();
});

export default router;