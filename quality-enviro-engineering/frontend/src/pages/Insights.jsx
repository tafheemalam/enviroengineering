import { useSearchParams, Link } from 'react-router-dom';
import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import { useSite } from '../hooks/useSite';
import SEO, { breadcrumbSchema } from '../components/SEO';
import { PostCard } from '../components/cards';
import { PageHeader, Loader, ErrorState } from '../components/ui';
import NotFound from './NotFound';

const PER_PAGE = 9;

export default function Insights() {
  const { settings } = useSite();
  const [params] = useSearchParams();
  const page = Math.max(1, parseInt(params.get('page') || '1', 10) || 1);
  const meta = useApi(() => api.page('insights'), [], '/pages/insights');
  const list = useApi(() => api.posts(page, PER_PAGE), [page], `/posts?page=${page}&limit=${PER_PAGE}`);

  if (!settings.show_insights) return <NotFound />;
  if (meta.loading || list.loading) return <Loader full />;
  if (list.error?.status === 404) return <NotFound />;
  if (list.error) return <ErrorState error={list.error} onRetry={list.retry} />;

  const intro = meta.data?.sections?.intro || {};
  const crumbs = [{ label: 'Insights', to: '/insights' }];
  const totalPages = Math.max(1, Math.ceil(list.data.total / PER_PAGE));

  return (
    <>
      <SEO
        seo={meta.data?.seo}
        robots={page > 1 ? 'noindex, follow' : undefined}
        jsonLd={[breadcrumbSchema(settings.site_url || window.location.origin, [{ label: 'Home', to: '/' }, ...crumbs])]}
      />
      <PageHeader eyebrow={intro.eyebrow} title={intro.title || 'Insights & Updates'} subtitle={intro.subtitle} crumbs={crumbs} />
      <section className="section">
        <div className="container">
          {list.data.items.length === 0 ? (
            <p className="text-center">No articles have been published yet. Please check back soon.</p>
          ) : (
            <div className="row g-4">
              {list.data.items.map((p, i) => (
                <div className="col-md-6 col-lg-4 d-flex" key={p.id}>
                  <PostCard post={p} delay={(i % 3) * 90} />
                </div>
              ))}
            </div>
          )}
          {totalPages > 1 && (
            <nav className="pagination-brand" aria-label="Insights pages">
              {Array.from({ length: totalPages }).map((_, i) => (
                <Link key={i} to={i === 0 ? '/insights' : `/insights?page=${i + 1}`} className={page === i + 1 ? 'active' : ''} aria-current={page === i + 1 ? 'page' : undefined}>
                  {i + 1}
                </Link>
              ))}
            </nav>
          )}
        </div>
      </section>
    </>
  );
}
