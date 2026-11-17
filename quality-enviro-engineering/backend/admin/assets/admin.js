/* QES admin panel interactions (no dependencies besides Bootstrap) */
(function () {
  'use strict';

  // ---- Confirm dangerous actions --------------------------------------
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn && !window.confirm(btn.getAttribute('data-confirm'))) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
    var bulk = e.target.closest('[data-confirm-bulk]');
    if (bulk) {
      var sel = document.getElementById('bulk');
      var count = document.querySelectorAll('.row-check:checked').length;
      if (!sel.value) { e.preventDefault(); alert('Choose a bulk action first.'); return; }
      if (!count) { e.preventDefault(); alert('Select at least one row.'); return; }
      if (sel.value === 'delete' && !confirm('Delete ' + count + ' inquiries permanently?')) e.preventDefault();
    }
  });

  // ---- Select all -----------------------------------------------------
  var checkAll = document.getElementById('checkAll');
  if (checkAll) {
    checkAll.addEventListener('change', function () {
      document.querySelectorAll('.row-check').forEach(function (c) { c.checked = checkAll.checked; });
    });
  }

  // ---- Slug auto-generation ------------------------------------------
  function slugify(s) {
    return s.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/&/g, ' and ')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 180);
  }
  document.querySelectorAll('[data-slug-source]').forEach(function (src) {
    var target = document.querySelector(src.getAttribute('data-slug-source'));
    if (!target) return;
    var auto = target.getAttribute('data-auto') === '1' || target.value === '';
    target.addEventListener('input', function () { auto = false; });
    src.addEventListener('input', function () { if (auto) target.value = slugify(src.value); });
  });

  // ---- Character counters + live SERP preview -------------------------
  document.querySelectorAll('[data-counter]').forEach(function (el) {
    var ideal = parseInt(el.getAttribute('data-ideal') || el.getAttribute('maxlength') || '0', 10);
    var label = el.closest('.mb-3, .col-12, div') && el.parentElement.querySelector('label');
    var counter = document.createElement('span');
    counter.className = 'char-counter';
    counter.setAttribute('aria-live', 'polite');
    (label || el.parentElement).appendChild(counter);
    var preview = el.getAttribute('data-preview') ? document.querySelector(el.getAttribute('data-preview')) : null;
    function update() {
      var n = el.value.length;
      counter.textContent = n + (ideal ? ' / ' + ideal : '');
      counter.classList.toggle('over', ideal && n > ideal);
      if (preview && el.value) preview.textContent = el.value;
    }
    el.addEventListener('input', update);
    update();
  });

  // ---- Image field: preview + media library ---------------------------
  var activeField = null;
  var libraryEl = document.getElementById('mediaLibrary');
  var libraryModal = libraryEl && window.bootstrap ? new bootstrap.Modal(libraryEl) : null;

  document.querySelectorAll('.image-field').forEach(function (wrap) {
    var input = wrap.querySelector('input[type=file]');
    var preview = wrap.querySelector('.img-preview');
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (!f) return;
      if (f.size > 5 * 1024 * 1024) alert('This file is larger than 5 MB and may be rejected.');
      var url = URL.createObjectURL(f);
      preview.classList.remove('empty');
      preview.innerHTML = '<img alt="">';
      preview.querySelector('img').src = url;
      wrap.querySelector('input[name$="_library"]').value = '';
    });
    var openBtn = wrap.querySelector('.js-open-library');
    if (openBtn) {
      openBtn.addEventListener('click', function () {
        if (!libraryModal) { alert('Upload images in the Media section first.'); return; }
        activeField = wrap;
        libraryModal.show();
      });
    }
  });
  if (libraryEl) {
    libraryEl.addEventListener('click', function (e) {
      var pick = e.target.closest('.media-pick');
      if (!pick || !activeField) return;
      activeField.querySelector('input[name$="_library"]').value = pick.getAttribute('data-path');
      activeField.querySelector('input[type=file]').value = '';
      var preview = activeField.querySelector('.img-preview');
      preview.classList.remove('empty');
      preview.innerHTML = '<img alt="">';
      preview.querySelector('img').src = pick.getAttribute('data-url');
      libraryModal.hide();
    });
  }

  // ---- Drag & drop upload --------------------------------------------
  var dz = document.getElementById('dropzone');
  if (dz) {
    var fileInput = document.getElementById('files');
    ['dragenter', 'dragover'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('drag'); });
    });
    dz.addEventListener('drop', function (e) {
      if (e.dataTransfer && e.dataTransfer.files.length) fileInput.files = e.dataTransfer.files;
    });
  }

  // ---- Copy to clipboard ---------------------------------------------
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.js-copy');
    if (!b) return;
    var text = location.origin + b.getAttribute('data-copy');
    (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).then(function () {
      var old = b.innerHTML;
      b.innerHTML = '<i class="bi bi-check2"></i> Copied';
      setTimeout(function () { b.innerHTML = old; }, 1500);
    }).catch(function () { prompt('Copy this URL:', text); });
  });

  // ---- Rich text editor ----------------------------------------------
  document.querySelectorAll('.rich-editor').forEach(function (wrap) {
    var area = wrap.querySelector('.re-area');
    var source = wrap.querySelector('.re-source');
    var sourceMode = false;
    area.innerHTML = source.value;
    try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (err) { /* ignore */ }

    function sync() { if (!sourceMode) source.value = area.innerHTML; }
    area.addEventListener('input', sync);
    area.addEventListener('blur', sync);
    // paste as plain text to avoid messy Word/website markup
    area.addEventListener('paste', function (e) {
      e.preventDefault();
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });

    wrap.querySelectorAll('[data-cmd]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (sourceMode) return;
        area.focus();
        var cmd = btn.getAttribute('data-cmd');
        var arg = btn.getAttribute('data-arg') || null;
        if (cmd === 'createLink') {
          arg = prompt('Link URL (https://… or /page):', 'https://');
          if (!arg || !/^(https?:\/\/|\/|mailto:|tel:)/i.test(arg)) return;
        }
        document.execCommand(cmd, false, arg);
        sync();
      });
    });
    wrap.querySelector('[data-toggle-source]').addEventListener('click', function () {
      sourceMode = !sourceMode;
      if (sourceMode) {
        source.value = area.innerHTML;
        area.classList.add('d-none');
        source.classList.remove('d-none');
      } else {
        area.innerHTML = source.value;
        source.classList.add('d-none');
        area.classList.remove('d-none');
      }
      this.classList.toggle('active', sourceMode);
    });
    var form = wrap.closest('form');
    if (form) form.addEventListener('submit', function () { if (!sourceMode) source.value = area.innerHTML; });
  });

  // ---- Repeater for section items --------------------------------------
  var editor = document.getElementById('itemsEditor');
  if (editor) {
    var hidden = document.getElementById('items_json');
    var icons = JSON.parse(editor.getAttribute('data-icons') || '[]');
    var items = [];
    try { items = JSON.parse(hidden.value || '[]'); } catch (err) { items = []; }

    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function save() { hidden.value = JSON.stringify(items); }
    function render() {
      editor.innerHTML = '';
      if (!items.length) editor.innerHTML = '<p class="text-muted small">No items yet.</p>';
      items.forEach(function (it, i) {
        var row = document.createElement('div');
        row.className = 'item-row';
        var opts = '<option value="">— icon —</option>' + icons.map(function (n) {
          return '<option' + (n === it.icon ? ' selected' : '') + '>' + esc(n) + '</option>';
        }).join('');
        row.innerHTML =
          '<div class="d-flex align-items-center gap-2 mb-2"><span class="item-num">#' + (i + 1) + '</span>' +
          '<div class="btn-group btn-group-sm ms-auto">' +
          '<button type="button" class="btn btn-light" data-act="up" aria-label="Move up"' + (i === 0 ? ' disabled' : '') + '><i class="bi bi-arrow-up"></i></button>' +
          '<button type="button" class="btn btn-light" data-act="down" aria-label="Move down"' + (i === items.length - 1 ? ' disabled' : '') + '><i class="bi bi-arrow-down"></i></button>' +
          '<button type="button" class="btn btn-outline-danger" data-act="del" aria-label="Remove item"><i class="bi bi-trash"></i></button></div></div>' +
          '<div class="row g-2"><div class="col-md-8"><input class="form-control" data-k="title" placeholder="Title" maxlength="150" aria-label="Item ' + (i + 1) + ' title" value="' + esc(it.title) + '"></div>' +
          '<div class="col-md-4"><select class="form-select" data-k="icon" aria-label="Item ' + (i + 1) + ' icon">' + opts + '</select></div>' +
          '<div class="col-12"><textarea class="form-control" data-k="text" rows="2" placeholder="Description" maxlength="600" aria-label="Item ' + (i + 1) + ' description">' + esc(it.text) + '</textarea></div></div>';
        row.addEventListener('input', function (e) {
          var k = e.target.getAttribute('data-k');
          if (k) { items[i][k] = e.target.value; save(); }
        });
        row.addEventListener('change', function (e) {
          var k = e.target.getAttribute('data-k');
          if (k) { items[i][k] = e.target.value; save(); }
        });
        row.addEventListener('click', function (e) {
          var b = e.target.closest('[data-act]');
          if (!b) return;
          var act = b.getAttribute('data-act');
          if (act === 'del' && confirm('Remove this item?')) items.splice(i, 1);
          if (act === 'up' && i > 0) items.splice(i - 1, 0, items.splice(i, 1)[0]);
          if (act === 'down' && i < items.length - 1) items.splice(i + 1, 0, items.splice(i, 1)[0]);
          save();
          render();
        });
        editor.appendChild(row);
      });
    }
    document.getElementById('addItem').addEventListener('click', function () {
      items.push({ title: '', text: '', icon: '' });
      save();
      render();
      var last = editor.querySelector('.item-row:last-child input');
      if (last) last.focus();
    });
    render();
  }
})();
