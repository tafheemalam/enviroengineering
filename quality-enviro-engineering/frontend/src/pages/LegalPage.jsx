import { api } from '../services/api';
import { useApi } from '../hooks/useApi';
import SEO from '../components/SEO';
import { PageHeader, Loader, ErrorState } from '../components/ui';
import { formatDate } from '../utils/format';
import NotFound from './NotFound';

/** Privacy Policy / Terms & Conditions – content managed in Admin > Pages. */
export default function LegalPage({ pageKey, path }) {
  const { data, loading, error, retry } = useApi(() => api.page(pageKey), [pageKey], `/pages/${pageKey}`);

  if (loading) return <Loader full />;
  if (error?.status === 404) return <NotFound />;
  if (error) return <ErrorState error={error} onRetry={retry} />;

  return (
    <>
      <SEO seo={data.seo} />
      <PageHeader title={data.page.title} crumbs={[{ label: data.page.title, to: path }]} />
      <section className="section">
        <div className="container narrow">
          {data.page.updated_at && <p className="text-muted-brand small">Last updated: {formatDate(data.page.updated_at)}</p>}
          <div className="rich-content" dangerouslySetInnerHTML={{ __html: data.page.content || '' }} />
        </div>
      </section>
    </>
  );
}
