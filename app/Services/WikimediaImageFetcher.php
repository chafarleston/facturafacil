<?php

namespace App\Services;

class WikimediaImageFetcher
{
    private string $userAgent;
    private int $timeout;

    public function __construct(string $userAgent = 'FacturaFacil/1.0 (dev)', int $timeout = 25)
    {
        $this->userAgent = $userAgent;
        $this->timeout = $timeout;
    }

    /**
     * Busca imágenes para el query y descarga la primera válida a un archivo temporal.
     * Devuelve ['path' => ruta temporal, 'title' => ..., 'credit' => ...] o null.
     */
    public function download(string $query): ?array
    {
        foreach ($this->search($query) as $result) {
            $tmp = $this->fetch($result['url']);
            if ($tmp !== null) {
                return ['path' => $tmp, 'title' => $result['title'], 'credit' => $result['credit']];
            }
        }

        return null;
    }

    /**
     * @return array<int, array{title:string,url:string,credit:string}>
     */
    public function search(string $query): array
    {
        $params = [
            'action' => 'query',
            'format' => 'json',
            'generator' => 'search',
            'gsrsearch' => $query,
            'gsrnamespace' => '6',
            'gsrlimit' => '10',
            'prop' => 'imageinfo',
            'iiprop' => 'url|mime',
            'iiurlwidth' => '800',
        ];

        $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query($params);

        // Wikimedia limita peticiones en ráfaga (devuelve respuestas vacías temporalmente):
        // reintentamos con espera creciente si no llegan páginas.
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $json = $this->httpGet($url);
            if ($json !== null) {
                $data = json_decode($json, true);
                if (is_array($data) && isset($data['query'])) {
                    $pages = $data['query']['pages'] ?? [];
                    if (!empty($pages)) {
                        return $this->mapPages($pages);
                    }
                    if ($attempt >= 4) {
                        return [];
                    }
                }
            }
            sleep($attempt);
        }

        return [];
    }

    /**
     * @param array<string, mixed> $pages
     * @return array<int, array{title:string,url:string,credit:string}>
     */
    private function mapPages(array $pages): array
    {
        $out = [];

        foreach ($pages as $page) {
            $info = $page['imageinfo'][0] ?? null;
            if (!$info) {
                continue;
            }

            $mime = $info['mime'] ?? '';
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                continue;
            }

            $url = $info['thumburl'] ?? $info['url'] ?? null;
            if (!$url) {
                continue;
            }

            $out[] = [
                'title' => $page['title'] ?? '',
                'url' => $url,
                'credit' => '',
            ];
        }

        return $out;
    }

    private function fetch(string $url): ?string
    {
        $contents = $this->httpGet($url, true);
        if ($contents === null || strlen($contents) < 800) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'wimg');
        if ($tmp === false) {
            return null;
        }
        file_put_contents($tmp, $contents);

        if (@getimagesize($tmp) === false) {
            @unlink($tmp);
            return null;
        }

        return $tmp;
    }

    private function httpGet(string $url, bool $binary = false): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => $this->userAgent,
                'follow_location' => 1,
                'max_redirects' => 5,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $contents = @file_get_contents($url, false, $context);

        return $contents === false ? null : $contents;
    }
}
