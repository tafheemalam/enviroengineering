import { Link } from 'react-router-dom';
import { Phone, Mail, MapPin, Clock } from 'lucide-react';
import { useSite } from '../hooks/useSite';
import { serviceHref, telHref } from '../utils/format';
import SocialLinks from './SocialLinks';

export default function Footer() {
  const { settings, nav_services: services } = useSite();
  const year = new Date().getFullYear();

  return (
    <footer className="site-footer">
      <div className="container">
        <div className="row g-5">
          <div className="col-lg-4 col-md-6">
            <Link to="/" className="brand brand-footer" aria-label="Home">
              {settings.logo_url && <img src={settings.logo_url} alt="" width="60" height="53" loading="lazy" />}
              <span className="brand-text">
                <strong>{settings.company_short || 'QES'}</strong>
                <small>Enviro &amp; Engineering Services</small>
              </span>
            </Link>
            {settings.footer_description && <p className="mt-3">{settings.footer_description}</p>}
            <SocialLinks size={18} className="footer-social" />
          </div>

          <div className="col-lg-2 col-md-6 col-6">
            <h2 className="footer-title">Quick Links</h2>
            <ul className="footer-links">
              <li><Link to="/">Home</Link></li>
              <li><Link to="/about">About Us</Link></li>
              <li><Link to="/services">Services</Link></li>
              {settings.show_insights && <li><Link to="/insights">Insights</Link></li>}
              <li><Link to="/request-a-quote">Request a Quote</Link></li>
              <li><Link to="/contact">Contact Us</Link></li>
            </ul>
          </div>

          <div className="col-lg-3 col-md-6 col-6">
            <h2 className="footer-title">Our Services</h2>
            <ul className="footer-links">
              {services.slice(0, 6).map((s) => (
                <li key={s.id}><Link to={serviceHref(s)}>{s.title}</Link></li>
              ))}
            </ul>
          </div>

          <div className="col-lg-3 col-md-6">
            <h2 className="footer-title">Contact Info</h2>
            <ul className="footer-contact">
              {settings.address && <li><MapPin size={18} aria-hidden="true" /><span>{settings.address}</span></li>}
              {settings.phone && <li><Phone size={18} aria-hidden="true" /><a href={telHref(settings.phone)}>{settings.phone}</a></li>}
              {settings.phone_secondary && <li><Phone size={18} aria-hidden="true" /><a href={telHref(settings.phone_secondary)}>{settings.phone_secondary}</a></li>}
              {settings.company_email && <li><Mail size={18} aria-hidden="true" /><a href={`mailto:${settings.company_email}`}>{settings.company_email}</a></li>}
              {settings.business_hours && <li><Clock size={18} aria-hidden="true" /><span>{settings.business_hours}</span></li>}
            </ul>
          </div>
        </div>
      </div>

      <div className="footer-bottom">
        <div className="container d-flex flex-column flex-md-row justify-content-between gap-2">
          <p className="mb-0">&copy; {year} {settings.copyright_text || settings.company_name}</p>
          <ul className="footer-legal">
            <li><Link to="/privacy-policy">Privacy Policy</Link></li>
            <li><Link to="/terms-and-conditions">Terms &amp; Conditions</Link></li>
          </ul>
        </div>
      </div>
    </footer>
  );
}
