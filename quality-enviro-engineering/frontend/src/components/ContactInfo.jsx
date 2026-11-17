import { Phone, Mail, MapPin, Clock, MessageCircle, MapPinned, Globe } from 'lucide-react';
import { useSite } from '../hooks/useSite';
import { telHref, whatsappHref } from '../utils/format';

export function ContactInfoList({ compact = false }) {
  const { settings } = useSite();
  const wa = whatsappHref(settings.whatsapp);
  const items = [
    settings.phone && { icon: Phone, label: 'Phone', value: settings.phone, href: telHref(settings.phone) },
    settings.phone_secondary && { icon: Phone, label: 'Alternate Phone', value: settings.phone_secondary, href: telHref(settings.phone_secondary) },
    settings.company_email && { icon: Mail, label: 'Email', value: settings.company_email, href: `mailto:${settings.company_email}` },
    wa && { icon: MessageCircle, label: 'WhatsApp', value: settings.whatsapp, href: wa, external: true },
    settings.address && { icon: MapPin, label: 'Office Address', value: settings.address },
    settings.service_area && { icon: Globe, label: 'Service Area', value: settings.service_area },
    !compact && settings.business_hours && { icon: Clock, label: 'Business Hours', value: settings.business_hours },
  ].filter(Boolean);

  return (
    <ul className="contact-info-list">
      {items.map(({ icon: Icon, label, value, href, external }) => (
        <li key={label}>
          <span className="ci-icon" aria-hidden="true"><Icon size={20} /></span>
          <span>
            <small>{label}</small>
            {href ? (
              <a href={href} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>{value}</a>
            ) : (
              <span className="ci-value">{value}</span>
            )}
          </span>
        </li>
      ))}
    </ul>
  );
}

export function MapEmbed() {
  const { settings } = useSite();
  if (!settings.map_embed_url) {
    return (
      <div className="map-placeholder">
        <MapPinned size={40} aria-hidden="true" />
        <p>Our office location map will appear here.</p>
        {settings.address && <p className="small">{settings.address}</p>}
      </div>
    );
  }
  return (
    <div className="map-embed">
      <iframe
        src={settings.map_embed_url}
        title={`${settings.company_name} office location on Google Maps`}
        loading="lazy"
        referrerPolicy="no-referrer-when-downgrade"
        allowFullScreen
      />
    </div>
  );
}
