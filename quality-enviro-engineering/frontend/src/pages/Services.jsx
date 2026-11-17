import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import CTASection from '../components/CTASection';
import { ServiceCard } from '../components/cards';
import { PageHeader, Loader, ErrorState } from '../components/ui';

export default function Services() {
  const { settings } = useSite();
  const page = useApi(() => api.page('services'), [], '/pages/services');
  const list = useApi(() => api.services(), [], '/services');

  if (page.loading || list.loading) return <Loader full />;
  if (list.error) return <ErrorState error={list.error} onRetry={list.retry} />;

  const intro = page.data?.sections?.intro || {};
  const crumbs = [{ label: 'Services', to: '/services' }];
  const base = settings.site_url || window.location.origin;

  const itemList = {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    itemListElement: list.data.map((s, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: s.title,
      url: base + (s.page_type === 'detail' ? `/services/${s.slug}` : `/contact?service=${s.slug}`),
    })),
  };

  return (
    <>
      <SEO seo={page.data?.seo} jsonLd={[breadcrumbSchema(base, [{ label: 'Home', to: '/' }, ...crumbs]), itemList]} />
      <PageHeader
        eyebrow={intro.eyebrow || 'Our Services'}
        title={intro.title || 'Environmental & Engineering Services'}
        subtitle={intro.subtitle}
        crumbs={crumbs}
      />
      <section className="section">
        <div className="container">
          {list.data.length === 0 ? (
            <p className="text-center">Our services will be listed here soon. Please <a href="/contact">contact us</a> in the meantime.</p>
          ) : (
            <div className="row g-4">
              {list.data.map((s, i) => (
                <div className="col-md-6 col-lg-4 d-flex" key={s.id}>
                  <ServiceCard service={s} delay={(i % 3) * 90} headingLevel="h2" />
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
      <CTASection />
    </>
  );
}
