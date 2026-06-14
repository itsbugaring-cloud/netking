<?php

namespace App\Services\Ipam;

use App\Models\Ipam\IpamOlt;
use Illuminate\Support\Collection;

class BookmarkParserService
{
    /**
     * Parse Netscape-format HTML bookmark content and extract OLT entries.
     *
     * Extracts <A HREF="...">LinkText</A> tags, parses the hostname/IP from the URL,
     * validates it as a valid IPv4 address, and returns a collection of entries.
     *
     * @param string $htmlContent Raw HTML content from a Netscape bookmark export
     * @return Collection Collection of ['name' => string, 'ip_address' => string]
     */
    public function parse(string $htmlContent): Collection
    {
        $entries = collect();

        // Match all <A HREF="...">...</A> tags from the bookmark HTML
        preg_match_all('/<A\s[^>]*HREF="([^"]*)"[^>]*>(.*?)<\/A>/i', $htmlContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $url = $match[1];
            $name = trim(strip_tags($match[2]));

            // Parse the hostname/IP from the URL
            $ip = $this->extractIpFromUrl($url);

            if ($ip === null) {
                continue;
            }

            $entries->push([
                'name' => $name,
                'ip_address' => $ip,
            ]);
        }

        return $entries;
    }

    /**
     * Import parsed bookmark entries into the database.
     *
     * Creates IpamOlt records for entries that don't already exist (based on ip_address).
     * Records an audit log entry for each successfully imported OLT.
     *
     * @param Collection $entries Collection of ['name' => string, 'ip_address' => string]
     * @param string $actor The username performing the import
     * @return array ['created' => int, 'skipped' => int]
     */
    public function importToDatabase(Collection $entries, string $actor): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($entries as $entry) {
            $name = $entry['name'];
            $ip = $entry['ip_address'];

            // Skip if ip_address already exists in ipam_olts
            if (IpamOlt::where('ip_address', $ip)->exists()) {
                $skipped++;
                continue;
            }

            // Handle duplicate names by appending a suffix
            $originalName = $name;
            $counter = 1;
            while (IpamOlt::where('name', $name)->exists()) {
                $name = $originalName . ' (' . $counter . ')';
                $counter++;
            }

            $olt = IpamOlt::create([
                'name' => $name,
                'ip_address' => $ip,
            ]);

            // Record audit log entry for the imported OLT
            IpamAuditService::log('import', 'olt', $olt->id, "Imported OLT: {$name} ({$ip})");

            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    /**
     * Extract host (IP or domain, optionally with port) from a URL string.
     *
     * Handles all common MikroTik Winbox bookmark URL formats:
     *  - Bare IP:           192.168.1.1
     *  - Bare IP+port:      192.168.1.1:8291
     *  - With http scheme:  http://192.168.1.1
     *  - With port:         http://103.20.1.1:8080
     *  - Domain:            http://olt.domain.com
     *
     * @param string $url The URL to extract a host from
     * @return string|null The host (with optional port), or null if not parseable
     */
    private function extractIpFromUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // If no scheme present, prepend http:// so parse_url works correctly
        if (!preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $url)) {
            $url = 'http://' . $url;
        }

        $parsed = parse_url($url);

        if (empty($parsed['host'])) {
            return null;
        }

        $host = $parsed['host'];

        // Include port if present so port-forwarded OLTs on same IP are treated as unique
        if (isset($parsed['port'])) {
            $host .= ':' . $parsed['port'];
        }

        return $host;
    }
}
