import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle2 } from 'lucide-react';
import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO from '../components/SEO';
import CTASection from '../components/CTASection';
import { ServiceCard, PostCard, TestimonialCard } from '../components/cards';
import { ContactInfoList } from '../components/ContactInfo';
import { Reveal, SectionHeading, SmartLink, IconBadge, Loader, ErrorState, Paragraphs } from '../components/ui';
import { getIcon } from '../utils/icons';
import { serviceHref } from '../utils/format';

export default function Home() {
  const { settings } = useSite();
  const { data, loading, error, retry } = useApi(() => api.home(), [], '/home');

  if (loading) return <Loader full />;
  if (error) return <ErrorState error={error} onRetry={retry} />;

  const s = data.sections || {};
  const hero = s.hero || {};
  const services = data.services || [];

  return (
    <>
      <SEO seo={data.seo} />

      {/* HERO */}
      <section className="hero" aria-labelledby="hero-title">
        {hero.image && <img className="hero-bg" src={hero.image} alt="" fetchpriority="high" />}
        <div className="hero-overlay" aria-hidden="true" />
        <div className="container position-relative">
          <div className="row align-items-center g-5">
            <div className="col-lg-7">
              {hero.eyebrow && <span className="eyebrow light hero-eyebrow">{hero.eyebrow}</span>}
              <h1 id="hero-title">{hero.title || settings.company_name}</h1>
              {hero.subtitle && <p className="hero-tagline">{hero.subtitle}</p>}
              <Paragraphs items={hero.paragraphs} className="hero-text" />
              <div className="hero-actions">
                <SmartLink href={hero.button_link || '/services'} className="btn-brand btn-lg-brand" arrow>
                  {hero.button_text || 'Explore Our Services'}
                </SmartLink>
                <SmartLink href={hero.button2_link || '/contact'} className="btn-outline-light-brand btn-lg-brand">
                  {hero.button2_text || 'Contact Us'}
                </SmartLink>
              </div>
            </div>
            <div className="col-lg-5 d-none d-lg-block">
              <div className="hero-panel">
                <div className="hero-panel-head">
                  {settings.logo_url && <img src={settings.logo_url} alt={`${settings.company_name} logo`} width="88" height="78" />}
                  <div>
                    <strong>What we deliver</strong>
                    <span>Environmental &amp; engineering expertise</span>
                  </div>
                </div>
                <ul>
                  {services.slice(0, 5).map((sv) => {
                    const Icon = getIcon(sv.icon);
                    return (
                      <li key={sv.id}>
                        <Link to={serviceHref(sv)}>
                          <span className="hp-icon"><Icon size={18} aria-hidden="true" /></span>
                          {sv.title}
                          <ArrowRight size={16} className="ms-auto" aria-hidden="true" />
                        </Link>
                      </li>
                    );
                  })}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ABOUT */}
      {s.about && (
        <section className="section" aria-labelledby="about-title">
          <div className="container">
            <div className="row align-items-center g-5">
              <Reveal className="col-lg-6">
                <div className="about-media">
                  {s.about.image && <img src={s.about.image} alt={s.about.image_alt} loading="lazy" width="800" height="520" />}
                  <div className="about-badge">
                    <IconBadge name="Leaf" size={22} />
                    <span>{settings.tagline}</span>
                  </div>
                </div>
              </Reveal>
              <Reveal className="col-lg-6" delay={120}>
                {s.about.eyebrow && <span className="eyebrow">{s.about.eyebrow}</span>}
                <h2 id="about-title">{s.about.title}</h2>
                <Paragraphs items={s.about.paragraphs} />
                <div className="row g-3 my-2">
                  {s.about.items.map((it) => (
                    <div className="col-sm-6" key={it.title}>
                      <div className="mv-card">
                        <IconBadge name={it.icon} size={22} />
                        <h3>{it.title}</h3>
                        <p>{it.text}</p>
                      </div>
                    </div>
                  ))}
                </div>
                <SmartLink href={s.about.button_link || '/about'} className="btn-outline-brand" arrow>
                  {s.about.button_text || 'Read More'}
                </SmartLink>
              </Reveal>
            </div>
          </div>
        </section>
      )}

      {/* SERVICES */}
      <section className="section bg-soft" aria-labelledby="services-title">
        <div className="container">
          <SectionHeading
            eyebrow={s.services?.eyebrow || 'What We Do'}
            title={<span id="services-title">{s.services?.title || 'Our Services'}</span>}
            subtitle={s.services?.subtitle}
          />
          <div className="row g-4">
            {services.map((sv, i) => (
              <div className="col-md-6 col-lg-4 d-flex" key={sv.id}>
                <ServiceCard service={sv} delay={(i % 3) * 90} />
              </div>
            ))}
          </div>
          <div className="text-center mt-5">
            <Link to="/services" className="btn-brand">View All Services <ArrowRight size={18} aria-hidden="true" /></Link>
          </div>
        </div>
      </section>

      {/* SPOTLIGHT (e.g. water & process solutions) */}
      {s.spotlight && (
        <section className="section section-navy" aria-labelledby="spotlight-title">
          <div className="container position-relative">
            <div className="row align-items-center g-5">
              <Reveal className="col-lg-6">
                {s.spotlight.eyebrow && <span className="eyebrow light">{s.spotlight.eyebrow}</span>}
                <h2 id="spotlight-title" className="text-white">{s.spotlight.title}</h2>
                <Paragraphs items={s.spotlight.paragraphs} className="spotlight-text" />
                {s.spotlight.items.length > 0 && (
                  <ul className="spotlight-list">
                    {s.spotlight.items.map((it) => (
                      <li key={it.title}><CheckCircle2 size={18} aria-hidden="true" /> {it.title}</li>
                    ))}
                  </ul>
                )}
                {s.spotlight.button_text && (
                  <SmartLink href={s.spotlight.button_link || '/services'} className="btn-brand" arrow>
                    {s.spotlight.button_text}
                  </SmartLink>
                )}
              </Reveal>
              {s.spotlight.image && (
                <Reveal className="col-lg-6" delay={120}>
                  <div className="spotlight-media">
                    <img src={s.spotlight.image} alt={s.spotlight.image_alt} loading="lazy" width="800" height="560" />
                  </div>
                </Reveal>
              )}
            </div>
          </div>
        </section>
      )}

      {/* CAPABILITY LIST (e.g. calibration categories) */}
      {s.calibration && s.calibration.items.length > 0 && (
        <section className="section bg-soft" aria-labelledby="calibration-title">
          <div className="container">
            <SectionHeading eyebrow={s.calibration.eyebrow} title={<span id="calibration-title">{s.calibration.title}</span>} subtitle={s.calibration.subtitle} />
            <ul className="capability-grid">
              {s.calibration.items.map((it, i) => {
                const Icon = getIcon(it.icon, CheckCircle2);
                return (
                  <Reveal as="li" key={it.title} delay={(i % 4) * 60}>
                    <Icon size={18} aria-hidden="true" />
                    <span>{it.title}</span>
                  </Reveal>
                );
              })}
            </ul>
            {s.calibration.button_text && (
              <div className="text-center mt-4">
                <SmartLink href={s.calibration.button_link || '/contact'} className="btn-outline-brand" arrow>
                  {s.calibration.button_text}
                </SmartLink>
              </div>
            )}
          </div>
        </section>
      )}

      {/* WHY CHOOSE US */}
      {s.why && (
        <section className="section" aria-labelledby="why-title">
          <div className="container">
            <SectionHeading eyebrow={s.why.eyebrow} title={<span id="why-title">{s.why.title}</span>} subtitle={s.why.subtitle} />
            <div className="row g-4">
              {s.why.items.map((it, i) => (
                <div className="col-md-6 col-lg-4" key={it.title}>
                  <Reveal className="feature-card" delay={(i % 3) * 90}>
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

      {/* PROCESS */}
      {s.process && (
        <section className="section section-navy" aria-labelledby="process-title">
          <div className="container">
            <SectionHeading light eyebrow={s.process.eyebrow} title={<span id="process-title">{s.process.title}</span>} subtitle={s.process.subtitle} />
            <ol className="process-steps">
              {s.process.items.map((it, i) => {
                const Icon = getIcon(it.icon);
                return (
                  <Reveal as="li" key={it.title} delay={i * 90}>
                    <span className="step-num" aria-hidden="true">{String(i + 1).padStart(2, '0')}</span>
                    <span className="step-icon" aria-hidden="true"><Icon size={26} /></span>
                    <h3>{it.title}</h3>
                    <p>{it.text}</p>
                  </Reveal>
                );
              })}
            </ol>
          </div>
        </section>
      )}

      {/* CREDENTIALS */}
      {s.credentials && s.credentials.items.length > 0 && (
        <section className="section bg-soft" aria-labelledby="credentials-title">
          <div className="container">
            <SectionHeading eyebrow={s.credentials.eyebrow} title={<span id="credentials-title">{s.credentials.title}</span>} subtitle={s.credentials.subtitle} />
            <div className="row g-4 justify-content-center">
              {s.credentials.items.map((it, i) => (
                <div className="col-md-6 col-lg-5" key={it.title}>
                  <Reveal className="credential-card" delay={i * 90}>
                    <IconBadge name={it.icon || 'Award'} size={24} />
                    <div>
                      <h3>{it.title}</h3>
                      {it.text && <p>{it.text}</p>}
                    </div>
                  </Reveal>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* TESTIMONIALS (admin-enabled) */}
      {data.testimonials?.length > 0 && (
        <section className="section bg-soft" aria-labelledby="testimonials-title">
          <div className="container">
            <SectionHeading eyebrow={s.testimonials?.eyebrow} title={<span id="testimonials-title">{s.testimonials?.title || 'What Our Clients Say'}</span>} subtitle={s.testimonials?.subtitle} />
            <div className="row g-4">
              {data.testimonials.map((t, i) => (
                <div className="col-md-6 col-lg-4 d-flex" key={t.id}>
                  <TestimonialCard t={t} delay={(i % 3) * 90} />
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <CTASection section={s.cta} />

      {/* INSIGHTS */}
      {data.posts?.length > 0 && (
        <section className="section" aria-labelledby="insights-title">
          <div className="container">
            <SectionHeading eyebrow={s.insights?.eyebrow} title={<span id="insights-title">{s.insights?.title || 'Latest Insights'}</span>} subtitle={s.insights?.subtitle} />
            <div className="row g-4">
              {data.posts.map((p, i) => (
                <div className="col-md-6 col-lg-4 d-flex" key={p.id}>
                  <PostCard post={p} delay={i * 90} />
                </div>
              ))}
            </div>
            <div className="text-center mt-5">
              <Link to="/insights" className="btn-outline-brand">View All Insights <ArrowRight size={18} aria-hidden="true" /></Link>
            </div>
          </div>
        </section>
      )}

      {/* CONTACT PREVIEW */}
      <section className="section bg-soft" aria-labelledby="contact-preview-title">
        <div className="container">
          <div className="row g-5 align-items-center">
            <Reveal className="col-lg-5">
              {s.contact?.eyebrow && <span className="eyebrow">{s.contact.eyebrow}</span>}
              <h2 id="contact-preview-title">{s.contact?.title || 'Get In Touch'}</h2>
              {s.contact?.subtitle && <p className="lead-sub">{s.contact.subtitle}</p>}
              <ul className="check-list">
                <li><CheckCircle2 size={18} aria-hidden="true" /> Prompt response to every inquiry</li>
                <li><CheckCircle2 size={18} aria-hidden="true" /> Clear scope and transparent proposals</li>
                <li><CheckCircle2 size={18} aria-hidden="true" /> Solutions tailored to your site</li>
              </ul>
              <Link to="/contact" className="btn-brand mt-2">Contact Us <ArrowRight size={18} aria-hidden="true" /></Link>
            </Reveal>
            <Reveal className="col-lg-7" delay={120}>
              <div className="contact-preview-card">
                <ContactInfoList compact />
              </div>
            </Reveal>
          </div>
        </div>
      </section>
    </>
  );
}
