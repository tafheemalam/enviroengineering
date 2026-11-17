import { useEffect, useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { ArrowUp, MessageCircle } from 'lucide-react';
import { useSite } from '../hooks/useSite';
import { whatsappHref } from '../utils/format';

export function WhatsAppButton() {
  const { settings } = useSite();
  const href = whatsappHref(settings.whatsapp, `Hello ${settings.company_name}, I would like to know more about your services.`);
  if (!settings.show_whatsapp || !href) return null;
  return (
    <a className="whatsapp-float" href={href} target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp (opens in a new tab)">
      <MessageCircle size={28} aria-hidden="true" />
    </a>
  );
}

export function BackToTop() {
  const [show, setShow] = useState(false);
  useEffect(() => {
    const onScroll = () => setShow(window.scrollY > 600);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);
  return (
    <button
      type="button"
      className={`back-to-top ${show ? 'show' : ''}`}
      onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
      aria-label="Back to top"
      tabIndex={show ? 0 : -1}
    >
      <ArrowUp size={20} aria-hidden="true" />
    </button>
  );
}

/** Scroll to top on route change, and notify GA4 of SPA page views. */
export function RouteEffects() {
  const { pathname, search } = useLocation();
  const first = useRef(true);
  useEffect(() => {
    window.scrollTo(0, 0);
    // gtag('config') already records the initial page view
    if (first.current) {
      first.current = false;
      return;
    }
    if (typeof window.gtag === 'function') {
      window.gtag('event', 'page_view', { page_path: pathname + search, page_location: window.location.href });
    }
  }, [pathname, search]);
  return null;
}
