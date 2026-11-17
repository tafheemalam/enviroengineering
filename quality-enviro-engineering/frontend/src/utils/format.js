/** Where a service card should link: detail page or contact form with the service preselected. */
export function serviceHref(service) {
  return service.page_type === 'detail'
    ? `/services/${service.slug}`
    : `/contact?service=${encodeURIComponent(service.slug)}`;
}

export function formatDate(value) {
  if (!value) return '';
  const d = new Date(String(value).replace(' ', 'T') + 'Z');
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
}

export function telHref(phone) {
  return 'tel:' + String(phone || '').replace(/[^\d+]/g, '');
}

export function whatsappHref(number, text = '') {
  const digits = String(number || '').replace(/\D/g, '');
  if (!digits) return null;
  return `https://wa.me/${digits}${text ? `?text=${encodeURIComponent(text)}` : ''}`;
}

/** Internal links render with <Link>, everything else with <a>. */
export function isInternal(href) {
  return typeof href === 'string' && href.startsWith('/') && !href.startsWith('//');
}
