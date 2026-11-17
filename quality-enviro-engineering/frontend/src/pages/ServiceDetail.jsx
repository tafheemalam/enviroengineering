import { Link, Navigate, useParams } from 'react-router-dom';
import { CheckCircle2, ArrowRight, Phone, Mail } from 'lucide-react';
import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import CTASection from '../components/CTASection';
import { ServiceCard } from '../components/cards';
import { PageHeader, Reveal, Loader, ErrorState } from '../components/ui';
import { serviceHref, telHref } from '../utils/format';
import NotFound from './NotFound';

export default function ServiceDetail() {
  const { slug } = useParams();
  const { settings, nav_services: navServices } = useSite();
  const { data: s, loading, error, retry } = useApi(() => api.service(slug), [slug], `/services/${slug}`);

  // Contact-type services have no detail page: send visitors to the form
  const navMatch = navServices.find((x) => x.slug === slug);
  if (navMatch && navMatch.page_type === 'contact') {
    return <Navigate to={serviceHref(navMatch)} replace />;
  }

  if (loading) return <Loader full />;
  if (error?.status === 404) return <NotFound />;
  if (error) return <ErrorState error={error} onRetry={retry} />;

  const base = settings.site_url || window.location.origin;
  const crumbs = [
    { label: 'Services', to: '/services' },
    { label: s.title, to: `/services/${s.slug}` },
  ];
  const serviceSchema = {
    '@context': 'https://schema.org',
    '@type': 'Service',
    name: s.title,
    serviceType: s.title,
    description: s.short_description,
    url: `${base}/services/${s.slug}`,
    image: s.image ? base + s.image : undefined,
    provider: { '@type': 'Organization', name: settings.company_name, url: base + '/' },
  };

  return (
    <>
      <SEO
        title={s.seo.title}
        description={s.seo.description}
        seo={{ keywords: s.seo.keywords }}
        image={s.seo.og_image}
        jsonLd={[breadcrumbSchema(base, [{ label: 'Home', to: '/' }, ...crumbs]), serviceSchema]}
      />
      <PageHeader eyebrow="Our Services" title={s.title} subtitle={s.short_description} crumbs={crumbs} />

      <section className="section">
        <div className="container">
          <div className="row g-5">
            <div className="col-lg-8">
              {s.image && (
                <Reveal className="detail-media">
                  <img src={s.image} alt={s.image_alt} width="800" height="520" />
                </Reveal>
              )}
              {s.full_description && (
                <div className="rich-content" dangerouslySetInnerHTML={{ __html: s.full_description }} />
              )}

              {s.key_features.length > 0 && (
                <div className="detail-block">
                  <h2>Key Features</h2>
                  <ul className="check-grid">
                    {s.key_features.map((f) => (
                      <li key={f}><CheckCircle2 size={20} aria-hidden="true" /> {f}</li>
                    ))}
                  </ul>
                </div>
              )}
              {s.benefits.length > 0 && (
                <div className="detail-block">
                  <h2>Benefits</h2>
                  <div className="row g-3">
                    {s.benefits.map((b, i) => (
                      <div className="col-sm-6" key={b}>
                        <div className="benefit-card">
                          <span className="benefit-num">{String(i + 1).padStart(2, '0')}</span>
                          <p>{b}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <div className="inquiry-banner">
                <div>
                  <h2>Interested in {s.title}?</h2>
                  <p>Send us an inquiry and our team will get back to you to discuss your requirements.</p>
                </div>
                <Link to={`/contact?service=${encodeURIComponent(s.slug)}`} className="btn-brand">
                  Send an Inquiry <ArrowRight size={18} aria-hidden="true" />
                </Link>
              </div>
            </div>

            <aside className="col-lg-4" aria-label="Service navigation and contact">
              <div className="sidebar-sticky">
                <div className="sidebar-card">
                  <h2 className="sidebar-title">All Services</h2>
                  <ul className="sidebar-links">
                    {navServices.map((x) => (
                      <li key={x.id}>
                        <Link to={serviceHref(x)} className={x.slug === s.slug ? 'active' : ''} aria-current={x.slug === s.slug ? 'page' : undefined}>
                          {x.title} <ArrowRight size={14} aria-hidden="true" />
                        </Link>
                      </li>
                    ))}
                  </ul>
                </div>
                <div className="sidebar-card navy">
                  <h2 className="sidebar-title">Need Help?</h2>
                  <p>Talk to our team about your project requirements.</p>
                  {settings.phone && <a href={telHref(settings.phone)}><Phone size={16} aria-hidden="true" /> {settings.phone}</a>}
                  {settings.company_email && <a href={`mailto:${settings.company_email}`}><Mail size={16} aria-hidden="true" /> {settings.company_email}</a>}
                  <Link to="/request-a-quote" className="btn-accent w-100 justify-content-center mt-3">Request a Quote</Link>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </section>

      {s.related?.length > 0 && (
        <section className="section bg-soft" aria-labelledby="related-title">
          <div className="container">
            <h2 id="related-title" className="text-center mb-5">Related Services</h2>
            <div className="row g-4">
              {s.related.map((r, i) => (
                <div className="col-md-6 col-lg-4 d-flex" key={r.id}>
                  <ServiceCard service={r} delay={i * 90} />
                </div>
              ))}
            </div>
          </div>
        </section>
      )}
      <CTASection />
    </>
  );
}
