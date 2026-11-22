export function studentSearchRequest(text) {
    const match = text.trim().match(/^(?:(?:tolong|mohon|coba)\s+)?(?:cari(?:kan)?|temukan|find|search)\s+(?:data\s+)?(?:siswa|murid|peserta didik)\s+(.+)$/i);
    if (!match || /\b(?:lalu|kemudian|setelah|dan (?:hapus|ubah|tambah|isi|buat))\b/i.test(match[1])) {
        return null;
    }
    const namePrefix = /^(?:(?:yang\s+)?(?:nama(?:nya)?|bernama)|dengan nama)\s*[:=]?\s+/i;
    if (!namePrefix.test(match[1]) && /^(?:yang|aktif|nonaktif|non-aktif|kelas|di kelas|status|semua|seluruh)\b/i.test(match[1])) {
        return null;
    }
    const query = match[1].replace(namePrefix, '').trim()
        .replace(/[?!]+$/, '').trim().replace(/^["'“‘]|["'”’]$/g, '').trim();

    return query && query.length <= 200 ? { resource: 'students', query } : null;
}
