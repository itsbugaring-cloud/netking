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
     * Extract a valid IPv4 address from a URL string.
     *
     * Parses the URL host component and validates it as an IPv4 address.
     * Handles URLs like http://192.168.1.1, http://10.10.50.1/path, etc.
     *
     * @param string $url The URL to extract an IP from
     * @return string|null The IPv4 address, or null if not a valid IPv4
     */
    private function extractIpFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);

        if (!isset($parsed['host'])) {
            return null;
        }

        $host = $parsed['host'];

        // Validate that the host is a valid IPv4 address
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return null;
        }

        return $host;
    }
}
