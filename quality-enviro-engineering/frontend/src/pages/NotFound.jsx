import { Link } from 'react-router-dom';
import { Home, Mail } from 'lucide-react';
import SEO from '../components/SEO';
import { useSite } from '../hooks/useSite';

export default function NotFound() {
  const { settings } = useSite();
  return (
    <>
      <SEO title={`Page Not Found | ${settings.company_name}`} description="The page you are looking for could not be found." robots="noindex, follow" />
      <section className="not-found">
        <div className="container text-center">
          <span className="nf-code" aria-hidden="true">404</span>
          <h1>Page not found</h1>
          <p>Sorry, the page you are looking for doesn&apos;t exist or has been moved.</p>
          <div className="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <Link to="/" className="btn-brand"><Home size={18} aria-hidden="true" /> Back to Home</Link>
            <Link to="/contact" className="btn-outline-brand"><Mail size={18} aria-hidden="true" /> Contact Us</Link>
          </div>
        </div>
      </section>
    </>
  );
}
