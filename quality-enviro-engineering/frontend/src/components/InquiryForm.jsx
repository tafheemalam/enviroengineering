import { useEffect, useRef, useState } from 'react';
import { CheckCircle2, Send, AlertTriangle } from 'lucide-react';
import { api } from '../services/api';

const EMPTY = {
  full_name: '',
  company_name: '',
  email: '',
  phone: '',
  service_id: '',
  subject: '',
  message: '',
  project_location: '',
  timeline: '',
  budget_range: '',
  website: '', // honeypot
};

const TIMELINES = ['As soon as possible', 'Within 1 month', '1–3 months', '3–6 months', 'Not sure yet'];

function validate(v, mode) {
  const e = {};
  if (v.full_name.trim().length < 2) e.full_name = 'Please enter your full name.';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.email.trim())) e.email = 'Please enter a valid email address.';
  if (!/^\+?[0-9\s().-]{7,20}$/.test(v.phone.trim())) e.phone = 'Please enter a valid phone number.';
  if (mode === 'contact' && v.subject.trim().length < 3) e.subject = 'Please enter a subject.';
  if (mode === 'quote' && !v.service_id) e.service_id = 'Please choose the service you need.';
  if (v.message.trim().length < 10) e.message = 'Please enter a message of at least 10 characters.';
  return e;
}

/**
 * Contact / quotation form.
 * @param {'contact'|'quote'} mode
 * @param {Array} services        services for the dropdown
 * @param {string} presetSlug     service slug to preselect (from ?service=)
 */
export default function InquiryForm({ mode = 'contact', services = [], presetSlug = '' }) {
  const [values, setValues] = useState(EMPTY);
  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState({ state: 'idle', message: '' });
  const started = useRef(Date.now());
  const formRef = useRef(null);
  const statusRef = useRef(null);

  // Preselect a service passed in the URL and pre-fill a helpful subject.
  useEffect(() => {
    if (!presetSlug || !services.length) return;
    const s = services.find((x) => x.slug === presetSlug);
    if (s) {
      setValues((v) => ({
        ...v,
        service_id: String(s.id),
        subject: v.subject || (mode === 'contact' ? `Inquiry about ${s.title}` : v.subject),
      }));
    }
  }, [presetSlug, services, mode]);

  const set = (field) => (e) => {
    const value = e.target.value;
    setValues((v) => ({ ...v, [field]: value }));
    if (errors[field]) setErrors((er) => ({ ...er, [field]: undefined }));
  };

  async function onSubmit(e) {
    e.preventDefault();
    if (status.state === 'sending') return;
    const found = validate(values, mode);
    setErrors(found);
    if (Object.keys(found).length) {
      const first = formRef.current?.querySelector(`[name="${Object.keys(found)[0]}"]`);
      first?.focus();
      return;
    }
    setStatus({ state: 'sending', message: '' });
    try {
      const payload = { ...values, service_id: values.service_id ? Number(values.service_id) : 0, _started: started.current };
      const res = mode === 'quote' ? await api.quote(payload) : await api.contact(payload);
      setStatus({ state: 'success', message: res.message });
      setValues(EMPTY);
      started.current = Date.now();
      if (typeof window.gtag === 'function') window.gtag('event', 'generate_lead', { form_type: mode });
    } catch (err) {
      if (err.errors && Object.keys(err.errors).length) setErrors(err.errors);
      setStatus({ state: 'error', message: err.message || 'Something went wrong. Please try again.' });
    }
    setTimeout(() => statusRef.current?.focus(), 50);
  }

  if (status.state === 'success') {
    return (
      <div className="form-success" role="status" tabIndex={-1} ref={statusRef}>
        <CheckCircle2 size={48} aria-hidden="true" />
        <h3>Thank you!</h3>
        <p>{status.message}</p>
        <button type="button" className="btn-outline-brand" onClick={() => setStatus({ state: 'idle', message: '' })}>
          Send another message
        </button>
      </div>
    );
  }

  const field = (name, label, props = {}, required = false) => (
    <div className={`form-field ${props.full ? 'col-12' : 'col-md-6'}`}>
      <label htmlFor={`f-${name}`}>
        {label} {required ? <span className="req" aria-hidden="true">*</span> : <span className="optional">(optional)</span>}
      </label>
      <input
        id={`f-${name}`}
        name={name}
        type={props.type || 'text'}
        value={values[name]}
        onChange={set(name)}
        autoComplete={props.autoComplete}
        maxLength={props.maxLength || 190}
        aria-required={required || undefined}
        aria-invalid={errors[name] ? 'true' : undefined}
        aria-describedby={errors[name] ? `e-${name}` : undefined}
        placeholder={props.placeholder}
      />
      {errors[name] && <span className="field-error" id={`e-${name}`}>{errors[name]}</span>}
    </div>
  );

  return (
    <form ref={formRef} className="inquiry-form" onSubmit={onSubmit} noValidate>
      {status.state === 'error' && (
        <div className="form-alert" role="alert" tabIndex={-1} ref={statusRef}>
          <AlertTriangle size={18} aria-hidden="true" /> {status.message}
        </div>
      )}
      <div className="row g-3">
        {field('full_name', 'Full Name', { autoComplete: 'name', maxLength: 120 }, true)}
        {field('company_name', 'Company Name', { autoComplete: 'organization', maxLength: 160 })}
        {field('email', 'Email Address', { type: 'email', autoComplete: 'email' }, true)}
        {field('phone', 'Phone Number', { type: 'tel', autoComplete: 'tel', maxLength: 40 }, true)}

        <div className="form-field col-md-6">
          <label htmlFor="f-service_id">
            Service Interested In {mode === 'quote' ? <span className="req" aria-hidden="true">*</span> : <span className="optional">(optional)</span>}
          </label>
          <select
            id="f-service_id"
            name="service_id"
            value={values.service_id}
            onChange={set('service_id')}
            aria-invalid={errors.service_id ? 'true' : undefined}
            aria-describedby={errors.service_id ? 'e-service_id' : undefined}
          >
            <option value="">Select a service…</option>
            {services.map((s) => (
              <option key={s.id} value={s.id}>{s.title}</option>
            ))}
          </select>
          {errors.service_id && <span className="field-error" id="e-service_id">{errors.service_id}</span>}
        </div>

        {mode === 'contact'
          ? field('subject', 'Subject', { maxLength: 200 }, true)
          : field('project_location', 'Project Location', { maxLength: 200, placeholder: 'City / site' })}

        {mode === 'quote' && (
          <>
            <div className="form-field col-md-6">
              <label htmlFor="f-timeline">Expected Timeline <span className="optional">(optional)</span></label>
              <select id="f-timeline" name="timeline" value={values.timeline} onChange={set('timeline')}>
                <option value="">Select…</option>
                {TIMELINES.map((t) => <option key={t} value={t}>{t}</option>)}
              </select>
            </div>
            {field('budget_range', 'Budget Range', { maxLength: 100, placeholder: 'Approximate budget' })}
          </>
        )}

        <div className="form-field col-12">
          <label htmlFor="f-message">
            {mode === 'quote' ? 'Project Details' : 'Message'} <span className="req" aria-hidden="true">*</span>
          </label>
          <textarea
            id="f-message"
            name="message"
            rows={6}
            value={values.message}
            onChange={set('message')}
            maxLength={5000}
            aria-required="true"
            aria-invalid={errors.message ? 'true' : undefined}
            aria-describedby={errors.message ? 'e-message' : undefined}
            placeholder={mode === 'quote' ? 'Describe the scope, site and any requirements…' : 'How can we help you?'}
          />
          {errors.message && <span className="field-error" id="e-message">{errors.message}</span>}
        </div>

        {/* Honeypot: hidden from people and assistive tech, bots fill it in */}
        <div className="hp-field" aria-hidden="true">
          <label htmlFor="f-website">Website</label>
          <input id="f-website" name="website" type="text" tabIndex={-1} autoComplete="off" value={values.website} onChange={set('website')} />
        </div>

        <div className="col-12 d-flex flex-wrap align-items-center gap-3">
          <button type="submit" className="btn-brand" disabled={status.state === 'sending'}>
            {status.state === 'sending' ? (
              <><span className="spinner sm" aria-hidden="true" /> Sending…</>
            ) : (
              <><Send size={18} aria-hidden="true" /> {mode === 'quote' ? 'Request Quotation' : 'Send Message'}</>
            )}
          </button>
          <small className="text-muted-brand">Fields marked <span className="req">*</span> are required. We never share your details.</small>
        </div>
      </div>
    </form>
  );
}
