import { Helmet } from 'react-helmet-async';
import { useLocation } from 'react-router-dom';
import { useSite } from '../hooks/useSite';

/**
 * Per-page meta tags for client-side navigation.
 * (The PHP shell already renders the same tags server-side for crawlers on first load.)
 */
export default function SEO({ seo = {}, title, description, image, type = 'website', robots, jsonLd = [] }) {
  const { settings } = useSite();
  const { pathname } = useLocation();
  const base = (settings.site_url || window.location.origin).replace(/\/$/, '');
  const pageTitle = title || seo.title || settings.company_name;
  const desc = description || seo.description || settings.tagline || '';
  const canonical = seo.canonical || base + (pathname === '/' ? '/' : pathname.replace(/\/$/, ''));
  const ogImage = image || seo.og_image || (settings.logo_url ? base + settings.logo_url : undefined);

  return (
    <Helmet prioritizeSeoTags>
      <title>{pageTitle}</title>
      <meta name="description" content={desc} />
      {seo.keywords && <meta name="keywords" content={seo.keywords} />}
      <meta name="robots" content={robots || seo.robots || 'index, follow'} />
      <link rel="canonical" href={canonical} />
      <meta property="og:type" content={type} />
      <meta property="og:site_name" content={settings.company_name} />
      <meta property="og:title" content={seo.og_title || pageTitle} />
      <meta property="og:description" content={seo.og_description || desc} />
      <meta property="og:url" content={canonical} />
      {ogImage && <meta property="og:image" content={ogImage} />}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={seo.og_title || pageTitle} />
      <meta name="twitter:description" content={seo.og_description || desc} />
      {ogImage && <meta name="twitter:image" content={ogImage} />}
      {jsonLd.map((obj, i) => (
        <script key={i} type="application/ld+json">{JSON.stringify(obj)}</script>
      ))}
    </Helmet>
  );
}

/** Build BreadcrumbList JSON-LD from [{label, to}] items. */
export function breadcrumbSchema(base, items) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((c, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: c.label,
      item: base.replace(/\/$/, '') + c.to,
    })),
  };
}
