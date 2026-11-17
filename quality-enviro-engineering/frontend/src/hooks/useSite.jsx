import { createContext, useContext, useEffect, useState } from 'react';
import { api, prime } from '../services/api';

const SiteContext = createContext(null);

const DEFAULTS = {
  settings: {
    company_name: 'Quality Enviro & Engineering Services',
    company_short: 'QES',
    tagline: 'Quality Solutions. Environmental Responsibility. Engineering Excellence.',
    show_insights: true,
    show_testimonials: false,
    show_whatsapp: true,
  },
  social: [],
  nav_services: [],
};

/**
 * Global site data (settings, social links, services for navigation).
 * The PHP shell inlines it as window.__QES_BOOT__ so no request is needed on first load.
 */
export function SiteProvider({ children }) {
  const boot = typeof window !== 'undefined' ? window.__QES_BOOT__ : null;
  const [site, setSite] = useState(boot || null);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (boot) {
      prime('/bootstrap', boot);
      return;
    }
    api.bootstrap().then(setSite).catch(setError);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const value = {
    ...(site || DEFAULTS),
    settings: { ...DEFAULTS.settings, ...(site?.settings || {}) },
    ready: !!site,
    error,
  };
  return <SiteContext.Provider value={value}>{children}</SiteContext.Provider>;
}

export function useSite() {
  const ctx = useContext(SiteContext);
  if (!ctx) throw new Error('useSite must be used inside <SiteProvider>');
  return ctx;
}
