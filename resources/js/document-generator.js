import html2pdf from 'html2pdf.js';

document.addEventListener('DOMContentLoaded', () => {
    // State
    let currentTab = 'nota';

    // Elements
    const tabNota = document.getElementById('tabNota');
    const tabSppd = document.getElementById('tabSppd');
    const inputsNota = document.getElementById('inputsNota');
    const inputsSppd = document.getElementById('inputsSppd');
    const previewNota = document.getElementById('previewNota');
    const previewSppd = document.getElementById('previewSppd');
    
    // Inputs that trigger updates
    const allInputs = document.querySelectorAll('input, textarea');

    // Init
    init();

    function init() {
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const dateInputs = ['notaDate', 'sppdSignDate'];
        dateInputs.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.value = today;
        });

        // Event Listeners
        tabNota?.addEventListener('click', () => switchTab('nota'));
        tabSppd?.addEventListener('click', () => switchTab('sppd'));
        
        document.getElementById('btnAddNotaBasis')?.addEventListener('click', () => addList('notaBasisContainer', 'nota-basis-input', 'prevNotaBasisList'));
        document.getElementById('btnAddSppdRemember')?.addEventListener('click', () => addList('sppdRememberContainer', 'sppd-rem-input', 'prevSppdRememberList'));
        document.getElementById('btnAddSppdCC')?.addEventListener('click', () => addList('sppdCCContainer', 'sppd-cc-input', 'prevSppdCCList'));

        document.getElementById('btnDownload')?.addEventListener('click', downloadPDF);
        document.getElementById('btnReset')?.addEventListener('click', resetForm);

        // Input listeners
        allInputs.forEach(input => {
            input.addEventListener('input', updateAll);
        });

        // Initial update
        updateAll();
    }

    function switchTab(tab) {
        currentTab = tab;
        
        // UI Classes
        if (tab === 'nota') {
            tabNota.className = 'flex-1 py-2 px-4 rounded font-bold border transition tab-active';
            tabSppd.className = 'flex-1 py-2 px-4 rounded font-bold border transition tab-inactive';
            inputsNota.classList.remove('hidden');
            inputsSppd.classList.add('hidden');
            previewNota.classList.remove('hidden');
            previewSppd.classList.add('hidden');
        } else {
            tabNota.className = 'flex-1 py-2 px-4 rounded font-bold border transition tab-inactive';
            tabSppd.className = 'flex-1 py-2 px-4 rounded font-bold border transition tab-active';
            inputsNota.classList.add('hidden');
            inputsSppd.classList.remove('hidden');
            previewNota.classList.add('hidden');
            previewSppd.classList.remove('hidden');
        }

        updateAll();
    }

    function updateAll() {
        // Shared
        const docNum = document.getElementById('docNumber')?.value || '...';
        document.querySelectorAll('.prev-docNum').forEach(el => el.innerText = docNum);

        if (currentTab === 'nota') {
            setText('prevNotaTo', 'notaTo');
            setText('prevNotaFrom', 'notaFrom');
            setText('prevNotaAtt', 'notaAtt');
            setText('prevNotaSubject', 'notaSubject');
            setText('prevNotaContent', 'notaContent');
            setText('prevNotaLoc', 'notaLoc');
            setText('prevNotaPos', 'notaPos');
            setText('prevNotaDiv', 'notaDiv');
            setText('prevNotaName', 'notaName');
            
            setDate('prevNotaDate', 'notaDate');
            updateList('notaBasisContainer', 'prevNotaBasisList', 'nota-basis-input');
        } 
        else if (currentTab === 'sppd') {
            setText('prevSppdWeigh', 'sppdWeigh');
            setText('prevSppdTo', 'sppdTo');
            setText('prevSppdTask', 'sppdTask');
            setText('prevSppdDest', 'sppdDest');
            setText('prevSppdTransport', 'sppdTransport');
            setText('prevSppdFunding', 'sppdFunding');
            setText('prevSppdReport', 'sppdReport');
            setText('prevSppdClose', 'sppdClose');
            setText('prevSppdLoc', 'sppdLoc');
            setText('prevSppdSignPos', 'sppdSignPos');
            setText('prevSppdSignName', 'sppdSignName');

            setDate('prevSppdDateGo', 'sppdDateGo');
            setDate('prevSppdDateBack', 'sppdDateBack');
            setDate('prevSppdSignDate', 'sppdSignDate');

            updateList('sppdRememberContainer', 'prevSppdRememberList', 'sppd-rem-input');
            updateList('sppdCCContainer', 'prevSppdCCList', 'sppd-cc-input');
        }
    }

    function setText(targetId, sourceId) {
        const source = document.getElementById(sourceId);
        const target = document.getElementById(targetId);
        if (source && target) {
            target.innerText = source.value.trim() ? source.value : '...';
        }
    }

    function setDate(targetId, sourceId) {
        const source = document.getElementById(sourceId);
        const target = document.getElementById(targetId);
        if (source && target) {
            target.innerText = getIndoDate(source.value);
        }
    }

    function getIndoDate(dateStr) {
        if (!dateStr) return '...';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function updateList(containerId, listId, inputClass) {
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        if(!container || !list) return;

        const inputs = container.getElementsByClassName(inputClass);
        list.innerHTML = '';
        
        let hasItem = false;
        Array.from(inputs).forEach(input => {
            if (input.value.trim()) {
                hasItem = true;
                const li = document.createElement('li');
                li.innerText = input.value;
                li.className = "mb-1 pl-1";
                list.appendChild(li);
            }
        });

        if (!hasItem) {
            const li = document.createElement('li');
            li.innerText = '...';
            li.style.listStyle = 'none';
            list.appendChild(li);
        }
    }

    function addList(containerId, inputClass, listId) {
        const container = document.getElementById(containerId);
        if(!container) return;

        const div = document.createElement('div');
        div.className = 'flex gap-2 item-row mt-2';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = `${inputClass} w-full p-2 border border-gray-300 rounded`;
        input.placeholder = 'Poin selanjutnya...';
        input.addEventListener('input', updateAll);

        const delBtn = document.createElement('button');
        delBtn.innerHTML = '&times;';
        delBtn.className = 'px-3 border rounded text-red-500 hover:bg-red-50 cursor-pointer';
        delBtn.type = 'button';
        delBtn.onclick = function() {
            container.removeChild(div);
            updateAll();
        };

        div.appendChild(input);
        div.appendChild(delBtn);
        container.appendChild(div);
    }

    function resetForm() {
        if(confirm("Reset semua input?")) {
            document.getElementById('mainForm').reset();
            
            // Reset Lists (Naive approach: remove all dynamic rows)
            ['notaBasisContainer', 'sppdRememberContainer', 'sppdCCContainer'].forEach(id => {
                const container = document.getElementById(id);
                // Keep the first one
                while (container.children.length > 1) {
                    container.removeChild(container.lastChild);
                }
                // Clear the first one
                container.querySelector('input').value = '';
            });
            
            // Reset dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('notaDate').value = today;
            document.getElementById('sppdSignDate').value = today;

            updateAll();
        }
    }

    function downloadPDF() {
        const element = document.getElementById('paperContent');
        const fileName = currentTab === 'nota' ? 'Nota_Dinas.pdf' : 'Surat_Perintah_SPPD.pdf';

        const images = element.getElementsByTagName('img');
        if(images.length > 0 && !images[0].complete) {
            images[0].onload = generate;
        } else {
            generate();
        }

        function generate() {
            element.style.minHeight = 'unset'; 
            const opt = {
                margin: 0,
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2, 
                    useCORS: true, 
                    allowTaint: true 
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save().then(() => {
                element.style.minHeight = '';
            });
        }
    }
});
