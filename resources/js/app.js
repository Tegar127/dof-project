import './bootstrap';

import Alpine from 'alpinejs';
import html2pdf from 'html2pdf.js';

import './auth/login';
import './dashboard/index';
import './editor/index';
import './admin/index';

window.Alpine = Alpine;
window.html2pdf = html2pdf;

Alpine.start();
import './document-generator';