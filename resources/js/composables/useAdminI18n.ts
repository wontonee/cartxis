import { useI18n } from 'vue-i18n';

export interface AdminMenuItem {
  id?: number;
  key?: string | null;
  title?: string;
  children?: AdminMenuItem[];
  [key: string]: unknown;
}

/**
 * Translate admin chrome and menu titles (Bagisto-style admin locale support).
 */
export function useAdminI18n() {
  const { t, te } = useI18n();

  const menuTitle = (item: AdminMenuItem | string | null | undefined, fallback?: string): string => {
    if (!item) {
      return fallback || '';
    }

    if (typeof item === 'string') {
      const key = `admin.menu.${item}`;
      return te(key) ? String(t(key)) : fallback || item;
    }

    const menuKey = item.key ? `admin.menu.${item.key}` : '';
    if (menuKey && te(menuKey)) {
      return String(t(menuKey));
    }

    return fallback || item.title || '';
  };

  const chrome = (key: string, fallback?: string): string => {
    const full = `admin.chrome.${key}`;
    return te(full) ? String(t(full)) : fallback || key;
  };

  const common = (key: string, fallback?: string, params?: Record<string, unknown>): string => {
    const full = `admin.common.${key}`;
    return te(full) ? String(t(full, params || {})) : fallback || key;
  };

  return { menuTitle, chrome, common, t, te };
}
