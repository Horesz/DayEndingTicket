<button onclick="exportToExcel()" 
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 inline-flex items-center">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    Excel Export (JS)
</button>

<script>
function exportToExcel() {
    // API hívás az adatokért
    const params = new URLSearchParams(window.location.search);
    
    fetch(`/riportok/export-json?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            // Excel munkakönyv létrehozása
            const workbook = XLSX.utils.book_new();
            
            // Adatok formázása
            const wsData = [
                // Fejléc
                ['Dátum', 'Fiók', 'Fiók kód', 'Dolgozó', 'Kártya bevétel', 'Készpénz bevétel', 
                 'Online bevétel', 'Egyéb bevétel', 'Össz bevétel', 'Napi bér', 'Költségek', 
                 'Össz kiadás', 'Eredmény', 'Státusz', 'Jóváhagyta', 'Jóváhagyva', 'Megjegyzés'],
                // Adatsorok
                ...data.map(item => [
                    item.datum,
                    item.fiok_nev,
                    item.fiok_kod,
                    item.dolgozo_nev,
                    parseFloat(item.kartya_bevetel),
                    parseFloat(item.keszpenz_bevetel),
                    parseFloat(item.online_bevetel),
                    parseFloat(item.egyeb_bevetel),
                    parseFloat(item.ossz_bevetel),
                    parseFloat(item.napi_ber),
                    parseFloat(item.koltsegek),
                    parseFloat(item.ossz_kiadas),
                    parseFloat(item.eredmeny),
                    item.statusz,
                    item.jovahagyta || '-',
                    item.jovahagyva_at || '-',
                    item.megjegyzes || ''
                ])
            ];
            
            // Munkalap létrehozása
            const worksheet = XLSX.utils.aoa_to_sheet(wsData);
            
            // Oszlopszélességek beállítása
            worksheet['!cols'] = [
                {wch: 12}, {wch: 20}, {wch: 10}, {wch: 20}, 
                {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, 
                {wch: 15}, {wch: 12}, {wch: 12}, {wch: 15}, 
                {wch: 15}, {wch: 12}, {wch: 20}, {wch: 18}, {wch: 30}
            ];
            
            // Munkalap hozzáadása a könyvhöz
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Napzárások');
            
            // Fájl letöltése
            const filename = `napzarasok_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(workbook, filename);
        })
        .catch(error => {
            console.error('Hiba:', error);
            alert('Hiba történt az export során!');
        });
}
</script>