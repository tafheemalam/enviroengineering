import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import CTASection from '../components/CTASection';
import { PageHeader, Reveal, SectionHeading, IconBadge, Loader, ErrorState, Paragraphs } from '../components/ui';
import NotFound from './NotFound';

export default function About() {
  const { settings } = useSite();
  const { data, loading, error, retry } = useApi(() => api.page('about'), [], '/pages/about');

  if (loading) return <Loader full />;
  if (error?.status === 404) return <NotFound />;
  if (error) return <ErrorState error={error} onRetry={retry} />;

  const s = data.sections || {};
  const crumbs = [{ label: 'About Us', to: '/about' }];

  return (
    <>
      <SEO seo={data.seo} jsonLd={[breadcrumbSchema(settings.site_url || window.location.origin, [{ label: 'Home', to: '/' }, ...crumbs])]} />
      <PageHeader eyebrow={s.intro?.eyebrow} title={s.intro?.title || 'About Us'} subtitle={s.intro?.subtitle} crumbs={crumbs} />

      {s.background && (
        <section className="section" aria-labelledby="bg-title">
          <div className="container">
            <div className="row g-5 align-items-center">
              <Reveal className="col-lg-6">
                {s.background.eyebrow && <span className="eyebrow">{s.background.eyebrow}</span>}
                <h2 id="bg-title">{s.background.title}</h2>
                <Paragraphs items={s.background.paragraphs} />
              </Reveal>
              {s.background.image && (
                <Reveal className="col-lg-6" delay={120}>
                  <div className="about-media">
                    <img src={s.background.image} alt={s.background.image_alt} loading="lazy" width="800" height="520" />
                  </div>
                </Reveal>
              )}
            </div>
          </div>
        </section>
      )}

      {s.mission_vision && (
        <section className="section bg-soft" aria-labelledby="mv-title">
          <div className="container">
            <SectionHeading eyebrow={s.mission_vision.eyebrow} title={<span id="mv-title">{s.mission_vision.title}</span>} />
            <div className="row g-4">
              {s.mission_vision.items.map((it, i) => (
                <div className="col-md-6" key={it.title}>
                  <Reveal className="mv-card large" delay={i * 100}>
                    <IconBadge name={it.icon} />
                    <h3>{it.title}</h3>
                    <p>{it.text}</p>
                  </Reveal>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {s.values && (
        <section className="section" aria-labelledby="values-title">
          <div className="container">
            <SectionHeading eyebrow={s.values.eyebrow} title={<span id="values-title">{s.values.title}</span>} subtitle={s.values.subtitle} />
            <div className="values-grid">
              {s.values.items.map((it, i) => (
                <Reveal className="value-item" key={it.title} delay={i * 70}>
                  <IconBadge name={it.icon} size={22} />
                  <h3>{it.title}</h3>
                  <p>{it.text}</p>
                </Reveal>
              ))}
            </div>
          </div>
        </section>
      )}

      {s.expertise && (
        <section className="section section-navy" aria-labelledby="exp-title">
          <div className="container">
            <SectionHeading light eyebrow={s.expertise.eyebrow} title={<span id="exp-title">{s.expertise.title}</span>} subtitle={s.expertise.subtitle} />
            <div className="row g-4">
              {s.expertise.items.map((it, i) => (
                <div className="col-sm-6 col-lg-3" key={it.title}>
                  <Reveal className="feature-card dark" delay={i * 80}>
                    <IconBadge name={it.icon} />
                    <h3>{it.title}</h3>
                    <p>{it.text}</p>
                  </Reveal>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {(s.quality || s.approach) && (
        <section className="section" aria-label="Quality and approach">
          <div className="container">
            <div className="row g-4">
              {[s.quality, s.approach].filter(Boolean).map((sec, i) => (
                <div className="col-lg-6" key={sec.key}>
                  <Reveal className="content-panel h-100" delay={i * 100}>
                    {sec.eyebrow && <span className="eyebrow">{sec.eyebrow}</span>}
                    <h2>{sec.title}</h2>
                    <Paragraphs items={sec.paragraphs} />
                  </Reveal>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {s.trust && (
        <section className="section bg-soft" aria-labelledby="trust-title">
          <div className="container">
            <SectionHeading eyebrow={s.trust.eyebrow} title={<span id="trust-title">{s.trust.title}</span>} subtitle={s.trust.subtitle} />
            <div className="row g-4">
              {s.trust.items.map((it, i) => (
                <div className="col-sm-6 col-lg-3" key={it.title}>
                  <Reveal className="feature-card" delay={i * 80}>
                    <IconBadge name={it.icon} />
                    <h3>{it.title}</h3>
                    <p>{it.text}</p>
                  </Reveal>
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
