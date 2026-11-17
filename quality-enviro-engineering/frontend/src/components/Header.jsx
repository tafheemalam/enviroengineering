import { useEffect, useRef, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import { Menu, X, Phone, Mail, ChevronDown, ArrowRight } from 'lucide-react';
import { useSite } from '../hooks/useSite';
import { serviceHref, telHref } from '../utils/format';
import SocialLinks from './SocialLinks';

export default function Header() {
  const { settings, nav_services: services } = useSite();
  const [open, setOpen] = useState(false);
  const [dropdown, setDropdown] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const dropRef = useRef(null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 10);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  // close menus on navigation
  useEffect(() => {
    setOpen(false);
    setDropdown(false);
  }, [location.pathname, location.search]);

  // lock body scroll when the mobile menu is open
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => (document.body.style.overflow = '');
  }, [open]);

  useEffect(() => {
    const onKey = (e) => e.key === 'Escape' && (setOpen(false), setDropdown(false));
    const onClick = (e) => dropRef.current && !dropRef.current.contains(e.target) && setDropdown(false);
    document.addEventListener('keydown', onKey);
    document.addEventListener('click', onClick);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.removeEventListener('click', onClick);
    };
  }, []);

  const links = [
    { to: '/', label: 'Home', end: true },
    { to: '/about', label: 'About Us' },
    { to: '/services', label: 'Services', dropdown: true },
    ...(settings.show_insights ? [{ to: '/insights', label: 'Insights' }] : []),
    { to: '/contact', label: 'Contact Us' },
  ];

  return (
    <header className={`site-header ${scrolled ? 'is-scrolled' : ''}`}>
      <div className="topbar d-none d-lg-block">
        <div className="container d-flex justify-content-between align-items-center">
          <div className="d-flex gap-4">
            {settings.phone && (
              <a href={telHref(settings.phone)}><Phone size={14} aria-hidden="true" /> {settings.phone}</a>
            )}
            {settings.company_email && (
              <a href={`mailto:${settings.company_email}`}><Mail size={14} aria-hidden="true" /> {settings.company_email}</a>
            )}
          </div>
          <SocialLinks size={14} className="topbar-social" />
        </div>
      </div>

      <nav className="mainnav" aria-label="Main navigation">
        <div className="container d-flex align-items-center justify-content-between">
          <Link to="/" className="brand" aria-label={`${settings.company_name} – Home`}>
            {settings.logo_url && <img src={settings.logo_url} alt="" width="52" height="46" />}
            <span className="brand-text">
              <strong>{settings.company_short || 'QES'}</strong>
              <small>Enviro &amp; Engineering Services</small>
            </span>
          </Link>

          <ul className="nav-links d-none d-lg-flex">
            {links.map((l) =>
              l.dropdown && services.length ? (
                <li key={l.to} className={`has-dropdown ${dropdown ? 'open' : ''}`} ref={dropRef}>
                  <NavLink to={l.to} className="nav-link-item">{l.label}</NavLink>
                  <button
                    type="button"
                    className="dropdown-toggle-btn"
                    aria-expanded={dropdown}
                    aria-label="Show services menu"
                    onClick={(e) => {
                      e.stopPropagation();
                      setDropdown((d) => !d);
                    }}
                  >
                    <ChevronDown size={16} />
                  </button>
                  <div className="nav-dropdown" role="menu">
                    {services.map((s) => (
                      <Link key={s.id} to={serviceHref(s)} role="menuitem">{s.title}</Link>
                    ))}
                    <Link to="/services" className="all" role="menuitem">View all services <ArrowRight size={14} /></Link>
                  </div>
                </li>
              ) : (
                <li key={l.to}>
                  <NavLink to={l.to} end={l.end} className="nav-link-item">{l.label}</NavLink>
                </li>
              )
            )}
          </ul>

          <div className="d-flex align-items-center gap-2">
            <Link to="/request-a-quote" className="btn-brand d-none d-md-inline-flex">Request a Quote</Link>
            <button
              type="button"
              className="menu-toggle d-lg-none"
              aria-label={open ? 'Close menu' : 'Open menu'}
              aria-expanded={open}
              aria-controls="mobile-menu"
              onClick={() => setOpen((o) => !o)}
            >
              {open ? <X size={26} /> : <Menu size={26} />}
            </button>
          </div>
        </div>
      </nav>

      <div id="mobile-menu" className={`mobile-menu ${open ? 'open' : ''}`} hidden={!open}>
        <ul>
          {links.map((l) => (
            <li key={l.to}><NavLink to={l.to} end={l.end}>{l.label}</NavLink></li>
          ))}
        </ul>
        <Link to="/request-a-quote" className="btn-brand w-100 justify-content-center">Request a Quote</Link>
        <div className="mobile-contact">
          {settings.phone && <a href={telHref(settings.phone)}><Phone size={16} /> {settings.phone}</a>}
          {settings.company_email && <a href={`mailto:${settings.company_email}`}><Mail size={16} /> {settings.company_email}</a>}
        </div>
      </div>
    </header>
  );
}
