function normalizePageName(value) {
    const aliases = [
        [/\b(?:add|create|buat|tambahkan)\b/g, 'tambah'],
        [/\b(?:material|materials)\b/g, 'materi'],
        [/\b(?:peserta didik|murid|kesiswaan)\b/g, 'siswa'],
        [/\b(?:rombongan belajar|rombel)\b/g, 'kelas'],
        [/\b(?:mata pelajaran|mapel)\b/g, 'mapel'],
        [/\b(?:materi pembelajaran|materi belajar|materi pelajaran)\b/g, 'materi'],
        [/\b(?:tugas pembelajaran|tugas belajar)\b/g, 'tugas'],
        [/\bstaff\b/g, 'staf'],
        [/\b(?:home|beranda)\b/g, 'dashboard'],
        [/\b(?:e rapor|e raport|raport)\b/g, 'rapor'],
        [/\bspp\b/g, 'tagihan siswa'],
        [/\b(?:surat menyurat|surat)\b/g, 'persuratan'],
        [/\bsettings\b/g, 'pengaturan'],
        [/\b(?:manajemen|pengelolaan|kelola|data|master|daftar|halaman|menu|modul|formulir|form)\b/g, ''],
    ];
    let text = value.toLocaleLowerCase('id-ID').normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '').replace(/&/g, ' dan ').replace(/[^a-z0-9]+/g, ' ');
    for (const [pattern, replacement] of aliases) {
        text = text.replace(pattern, replacement);
    }

    return [...new Set(text.trim().split(/\s+/).filter(Boolean))].join(' ');
}

export function navigationRequest(text) {
    const command = text.trim().replace(/^(?:(?:tolong|mohon|coba|silakan|silahkan|bisa(?:kah)?(?: kamu| anda)?)\s+)+/i, '');
    const match = command.match(/^(?:buka(?:kan)?|open|pergi ke|masuk ke|arahkan(?: saya)? ke|go to)\s+(?:(?:halaman|menu|modul|page)\s+)?(.+?)\s*[.!?]*$/i)
        || command.match(/^tampilkan\s+(?:halaman|menu|modul|page)\s+(.+?)\s*[.!?]*$/i);
    if (!match || /\b(?:lalu|kemudian|setelah itu|dan (?:hapus|ubah|tambah|tambahkan|isi|cari|buat|buka))\b/i.test(match[1])) {
        return null;
    }

    return match[1].replace(/[.!?]+$/, '').replace(/(?:\s+(?:dong|ya|sekarang|langsung|please|tolong))+$/i, '').trim();
}

export function resolveNavigation(text, items, currentUrl) {
    const query = navigationRequest(text);
    if (!query) {
        return null;
    }
    const normalized = normalizePageName(query);
    const isCreateRequest = normalized.split(' ').includes('tambah');
    const pages = [];
    for (const item of items) {
        try {
            const url = new URL(item.url, currentUrl);
            const current = new URL(currentUrl);
            if (url.origin !== current.origin || !['http:', 'https:'].includes(url.protocol)
                || url.hash || url.username || url.password) {
                continue;
            }
            const pageName = normalizePageName(item.label);
            if (!isCreateRequest && pageName.startsWith('tambah ')) {
                continue;
            }
            pages.push({ label: item.label.replace(/\s+/g, ' ').trim(), url: url.href, normalized: pageName });
        } catch {
            // Ignore a menu entry whose address cannot be resolved.
        }
    }
    const exact = pages.filter((page) => normalized && page.normalized === normalized);
    const matches = exact.length ? exact : pages.filter((page) => normalized
        && normalized.split(' ').every((term) => page.normalized.split(' ').includes(term)));

    return {
        query,
        matches: matches.filter((page, index, all) => all.findIndex((other) => other.url === page.url) === index)
            .map(({ label, url }) => ({ label, url })),
    };
}
