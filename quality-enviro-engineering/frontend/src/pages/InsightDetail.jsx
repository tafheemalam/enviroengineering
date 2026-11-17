import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, Calendar, User } from 'lucide-react';
import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import CTASection from '../components/CTASection';
import { PageHeader, Loader, ErrorState } from '../components/ui';
import { formatDate } from '../utils/format';
import NotFound from './NotFound';

export default function InsightDetail() {
  const { slug } = useParams();
  const { settings } = useSite();
  const { data: p, loading, error, retry } = useApi(() => api.post(slug), [slug], `/posts/${slug}`);

  if (!settings.show_insights) return <NotFound />;
  if (loading) return <Loader full />;
  if (error?.status === 404) return <NotFound />;
  if (error) return <ErrorState error={error} onRetry={retry} />;

  const base = settings.site_url || window.location.origin;
  const crumbs = [
    { label: 'Insights', to: '/insights' },
    { label: p.title, to: `/insights/${p.slug}` },
  ];
  const article = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: p.title,
    description: p.seo.description,
    image: p.seo.og_image || undefined,
    datePublished: p.published_at ? new Date(p.published_at.replace(' ', 'T') + 'Z').toISOString() : undefined,
    author: { '@type': 'Organization', name: p.author_name || settings.company_name },
    publisher: { '@type': 'Organization', name: settings.company_name },
    mainEntityOfPage: `${base}/insights/${p.slug}`,
  };

  return (
    <>
      <SEO
        title={p.seo.title}
        description={p.seo.description}
        seo={{ keywords: p.seo.keywords }}
        image={p.seo.og_image}
        type="article"
        jsonLd={[breadcrumbSchema(base, [{ label: 'Home', to: '/' }, ...crumbs]), article]}
      />
      <PageHeader eyebrow="Insights" title={p.title} crumbs={crumbs} />
      <section className="section">
        <div className="container narrow">
          <div className="post-meta-row">
            {p.published_at && <span><Calendar size={16} aria-hidden="true" /> <time dateTime={p.published_at}>{formatDate(p.published_at)}</time></span>}
            {p.author_name && <span><User size={16} aria-hidden="true" /> {p.author_name}</span>}
          </div>
          {p.image && (
            <div className="detail-media">
              <img src={p.image} alt={p.image_alt} width="800" height="520" />
            </div>
          )}
          <article className="rich-content" dangerouslySetInnerHTML={{ __html: p.content || '' }} />
          <Link to="/insights" className="btn-outline-brand mt-4"><ArrowLeft size={18} aria-hidden="true" /> Back to Insights</Link>
        </div>
      </section>
      <CTASection />
    </>
  );
}
