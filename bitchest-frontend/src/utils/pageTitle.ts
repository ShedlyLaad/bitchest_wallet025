/**
 * Utility to manage page titles and favicons dynamically based on routes
 */

interface RouteConfig {
  title: string;
  favicon: 'admin' | 'user' | 'default';
}

const routeTitles: Record<string, RouteConfig> = {
  // Admin routes
  'AdminHome': { title: 'Dashboard - Administration BitChest', favicon: 'admin' },
  'AdminUsers': { title: 'Users Management - Administration BitChest', favicon: 'admin' },
  'AdminMarket': { title: 'Market Overview - Administration BitChest', favicon: 'admin' },
  'AdminTransactions': { title: 'Transaction History - Administration BitChest', favicon: 'admin' },
  'AdminProfile': { title: 'Profile - Administration BitChest', favicon: 'admin' },
  
  // User routes
  'Dashboard': { title: 'Dashboard - BitChest', favicon: 'user' },
  'Profile': { title: 'Profile - BitChest', favicon: 'user' },
  'Trade': { title: 'Trade Cryptocurrencies - BitChest', favicon: 'user' },
  'Portfolio': { title: 'Wallet - BitChest', favicon: 'user' },
  'Support': { title: 'Support - BitChest', favicon: 'user' },
  
  // Public routes
  'Landing': { title: 'BitChest - Cryptocurrency Trading Platform', favicon: 'default' },
  'Signin': { title: 'Sign In - BitChest', favicon: 'default' },
  'Signup': { title: 'Sign Up - BitChest', favicon: 'default' },
  'ChangePassword': { title: 'Change Password - BitChest', favicon: 'user' },
};

/**
 * Update favicon based on type with multiple sizes for better clarity
 */
function updateFavicon(type: 'admin' | 'user' | 'default'): void {
  let faviconPath = '/LogoUser-icon.png'; // Default to user logo

  switch (type) {
    case 'admin':
      faviconPath = '/LogoAdmin-icon.png';
      break;
    case 'user':
      faviconPath = '/LogoUser-icon.png';
      break;
    case 'default':
      faviconPath = '/LogoUser-icon.png';
      break;
  }

  // Remove existing favicon and apple-touch-icon links
  const existingLinks = document.querySelectorAll("link[rel*='icon'], link[rel='apple-touch-icon']");
  existingLinks.forEach(link => link.remove());

  // Create multiple favicon links for different sizes and devices
  // Standard favicon (16x16)
  const favicon16 = document.createElement('link');
  favicon16.rel = 'icon';
  favicon16.type = 'image/png';
  favicon16.sizes = '16x16';
  favicon16.href = faviconPath;
  document.head.appendChild(favicon16);

  // Standard favicon (32x32) - better quality
  const favicon32 = document.createElement('link');
  favicon32.rel = 'icon';
  favicon32.type = 'image/png';
  favicon32.sizes = '32x32';
  favicon32.href = faviconPath;
  document.head.appendChild(favicon32);

  // High-resolution favicon (192x192) for modern browsers
  const favicon192 = document.createElement('link');
  favicon192.rel = 'icon';
  favicon192.type = 'image/png';
  favicon192.sizes = '192x192';
  favicon192.href = faviconPath;
  document.head.appendChild(favicon192);

  // Apple Touch Icon (180x180) - for iOS devices, displayed larger
  const appleTouchIcon = document.createElement('link');
  appleTouchIcon.rel = 'apple-touch-icon';
  appleTouchIcon.sizes = '180x180';
  appleTouchIcon.href = faviconPath;
  document.head.appendChild(appleTouchIcon);

  // Fallback shortcut icon for older browsers
  const shortcutIcon = document.createElement('link');
  shortcutIcon.rel = 'shortcut icon';
  shortcutIcon.type = 'image/png';
  shortcutIcon.href = faviconPath;
  document.head.appendChild(shortcutIcon);
}

/**
 * Update page title and favicon based on route name
 */
export function updatePageHead(routeName: string | symbol | undefined): void {
  if (!routeName || typeof routeName !== 'string') {
    // Default fallback
    document.title = 'BitChest - Cryptocurrency Trading Platform';
    updateFavicon('default');
    return;
  }

  const config = routeTitles[routeName];
  if (config) {
    document.title = config.title;
    updateFavicon(config.favicon);
  } else {
    // Fallback for unknown routes
    document.title = 'BitChest - Cryptocurrency Trading Platform';
    updateFavicon('default');
  }
}
