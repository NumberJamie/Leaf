<?php
class IssueParse {
    public string $localIssueDir = 'issues';
    private string $issuePattern = '/\/\/\s*TODO:\s*(.*)/';
    private string $localDir = __DIR__ . DIRECTORY_SEPARATOR;

    private function issueIterator(string $dir = null, array|null &$files = []): array|null {
        if (empty($dir)) {
            $dir = $this->localDir . $this->localIssueDir;
        }
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? self::issueIterator($path, $files) : $files[] = $path;
        }
        return $files;
    }

    function findIssues(string $file): array {
        $issues = [];
        foreach (file($file) as $line) {
            preg_match($this->issuePattern, $line, $matches);
            if (!$matches) continue;
            $issues[] = $matches[1];
        }
        return $issues;
    }

    function recursivelyFindIssues(): array {
        $issues = [];
        foreach (self::issueIterator() as $file) {
            $fileIssues = self::findIssues($file);
            $issues = array_merge($issues, $fileIssues);
        }
        return $issues;
    }
}

$parser = new IssueParse();
print_r($parser->recursivelyFindIssues());