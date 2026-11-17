import { useSearchParams } from 'react-router-dom';
import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import InquiryForm from '../components/InquiryForm';
import { ContactInfoList, MapEmbed } from '../components/ContactInfo';
import SocialLinks from '../components/SocialLinks';
import { PageHeader, Reveal, Loader } from '../components/ui';

/** Used for both /contact and /request-a-quote (mode="quote"). */
export default function Contact({ mode = 'contact' }) {
  const { settings, social } = useSite();
  const [params] = useSearchParams();
  const key = mode === 'quote' ? 'quote' : 'contact';
  const page = useApi(() => api.page(key), [key], `/pages/${key}`);
  const services = useApi(() => api.services(), [], '/services');

  if (page.loading) return <Loader full />;

  const intro = page.data?.sections?.intro || {};
  const path = mode === 'quote' ? '/request-a-quote' : '/contact';
  const label = mode === 'quote' ? 'Request a Quote' : 'Contact Us';
  const crumbs = [{ label, to: path }];
  const base = settings.site_url || window.location.origin;
  const preset = (params.get('service') || '').replace(/[^a-z0-9-]/gi, '');
  const presetService = services.data?.find((s) => s.slug === preset);

  return (
    <>
      <SEO
        seo={page.data?.seo || {}}
        jsonLd={[
          breadcrumbSchema(base, [{ label: 'Home', to: '/' }, ...crumbs]),
          {
            '@context': 'https://schema.org',
            '@type': 'ContactPage',
            name: label,
            url: base + path,
          },
        ]}
      />
      <PageHeader eyebrow={intro.eyebrow || label} title={intro.title || label} subtitle={intro.subtitle} crumbs={crumbs} />

      <section className="section">
        <div className="container">
          <div className="row g-5">
            <Reveal className="col-lg-7 order-2 order-lg-1">
              <div className="form-card">
                <h2>{mode === 'quote' ? 'Tell Us About Your Project' : 'Send Us a Message'}</h2>
                {presetService && (
                  <p className="preset-note">
                    You are enquiring about <strong>{presetService.title}</strong>.
                  </p>
                )}
                {services.error ? (
                  <p className="form-alert" role="alert">We could not load the services list. You can still reach us using the contact details.</p>
                ) : null}
                <InquiryForm mode={mode} services={services.data || []} presetSlug={preset} />
              </div>
            </Reveal>
            <Reveal className="col-lg-5 order-1 order-lg-2" delay={100}>
              <div className="contact-side">
                <h2>Contact Information</h2>
                <ContactInfoList />
                {social.length > 0 && (
                  <div className="mt-4">
                    <h3 className="h6 text-uppercase mb-3 fw-semibold">Follow Us</h3>
                    <SocialLinks size={18} className="contact-social" />
                  </div>
                )}
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      {mode === 'contact' && (
        <section className="map-section" aria-label="Office location map">
          <div className="container">
            <div className="row g-4 align-items-stretch">
              <div className="col-lg-8"><MapEmbed /></div>
              <div className="col-lg-4">
                <div className="map-side">
                  <h2>Visit Our Office</h2>
                  <ContactInfoList compact />
                </div>
              </div>
            </div>
          </div>
        </section>
      )}
    </>
  );
}
