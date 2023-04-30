<?php

class DepDash
{

    private $dir;
    public $results;
    private $config;

    public function __construct()
    {
        $this->config = (new Config())->init();
        $this->dir = $this->config['getRepositoriesPath'];
    }

    public function getResponse()
    {
        $subdirs = $this->getSubdirs();

        $results = [];
        foreach ($subdirs as $subdir) {
            $path = $this->dir . DIRECTORY_SEPARATOR . $subdir;
            chdir($path);
            $repositoryUrl = $this->getRepositoryUrl();
            $mergeVersion = $this->getMergeVersion();
            $environmentStatuses = $this->getEnvironmentStatuses($mergeVersion, $repositoryUrl, $subdir);
            $dockerStatuses = $this->getDockerStatuses();
            $newName = str_replace($this->config['projectRename']['default'], $this->config['projectRename']['new'], $subdir);
            $projectName = str_replace('-', ' ', ucwords($newName));
            $deploymentProject = [
                'deploymentProject' => [
                    'name' => $this->config['projectPrefix'] . '' . $projectName . '' . $this->config['projectSuffiks'],
                    'datetime' => date('Y-m-d H:i:s')
                ],
                'environmentStatuses' => $environmentStatuses,
                'dockerStatuses' => $dockerStatuses,
            ];

            $results[] = $deploymentProject;
        }
        $this->results = $this->mergeData($results);
        return $this;
    }

    private function mergeData($results)
    {
        $data = [];
        foreach ($this->config['externalData'] as $url) {
            $contents = json_decode(file_get_contents($url), true);
            $data = array_merge($data, $contents);
        }
        return array_merge($results, $data);
    }

    public function withJson()
    {
        $this->results = json_encode($this->results);
        print($this->results);
        return $this;
    }

    public function writeToFile()
    {
        file_put_contents($this->config['jsonFilePath'] . '/data.json', $this->results);
        return $this;
    }

    private function getDockerStatuses()
    {
        return [
            'docker_ps' => $this->getDockerPs(),
        ];
    }

    private function getEnvironmentStatuses($mergeVersion, $repositoryUrl, $subdir)
    {
        $envs = $this->config['enviroments'];

        $environmentStatuses = [];
        foreach ($envs as $env) {
            $repoResults = [];
            $error = 0;

            $currentBranch = $this->getCurrentBranch();
            if ($currentBranch) {
                $repoResults['deploymentVersionName'] = $currentBranch;

                $lastMergeDate = $this->getLastMergeDate($currentBranch);
                if ($lastMergeDate) {
                    $repoResults['lastMergeDay'] = $lastMergeDate;
                } else {
                    $error++;
                    $repoResults['lastMergeDay'] = '';
                }

                $lastPullDate = $this->getLastPullDate($currentBranch);
                if ($lastPullDate) {
                    $repoResults['finishedDate'] = $lastPullDate;
                } else {
                    $error++;
                    $repoResults['finishedDate'] = '';
                }

                $totalCommits = $this->getTotalCommits();
                if ($totalCommits) {
                    $repoResults['totalCommits'] = $totalCommits;
                } else {
                    $error++;
                    $repoResults['totalCommits'] = '';
                }

                $lastActivityBy = $this->getLastActionDeveloper($currentBranch);
                $lastMergeBy = $this->getMergedBy();
                if ($lastActivityBy) {
                    $repoResults['reasonSummary'] = ''
                        . '<b>Merged by</b>:' . $lastMergeBy . '<br/>'
                        . '<b>Deployed by</b>: ' . $lastActivityBy . '<br/><br/>'
                        . '<b>Last message:</b> <i>' . $this->getLastCommitMessage() . '</i>';
                } else {
                    $error++;
                    $repoResults['reasonSummary'] = '';
                }
            } else {
                $error++;
            }

            if ($error === 0) {
                $repoResults['deploymentState'] = 'SUCCESS';
            } else {
                $repoResults['deploymentState'] = 'FAILED';
            }

            $environmentStatuses[] = [
                'environment' => [
                    'id' => $mergeVersion,
                    'repoUrl' => $repositoryUrl,
                    'name' => $this->config['enviromentId']($env)
                ],
                'deploymentResult' => $repoResults
            ];
        }

        return $environmentStatuses;
    }

    private function getSubdirs()
    {
        $final = [];
        $dirs = array_diff(scandir($this->dir), array_merge(['..', '.', '.git'], $this->config['projectExclude']));
        foreach ($dirs as $dir) {
            if (is_dir($this->config['getRepositoriesPath'] . '/' . $dir)) {
                $final[] = $dir;
            }
        }
        return $final;
    }

    private function getRepositoryUrl()
    {
        $pattern = '/^(https?:\/\/)?([^@]+@)?([^\/:]+)[\/:]([^\/]+)\/([^\/]+)$/i';
        $url = exec("git config --get remote.origin.url");

        if (preg_match($pattern, $url, $matches)) {
            $urlWithoutGit = 'https://' . $matches[3] . '/' . $matches[4] . '/' . $matches[5];
            return explode('.git', $urlWithoutGit)[0];
        }
        return '';
    }

    private function getMergeVersion()
    {
        $res = exec('git rev-list --count --merges master');
        return ($res == 0) ? 1 : $res;
    }

    private function getLastPullDate($currentBranch)
    {
        $output = [];
        $status = null;
        exec('git log --remotes -n 1 --format="%ci" ' . $currentBranch, $output, $status);
        return $status === 0 ? $output[0] : '';
    }

    private function getLastPullPushDate($currentBranch)
    {
        $pullDate = trim(exec('git log -1 --format=%cd --date=local'));
        $pushDate = trim(exec("git log -1 --format=%cd --date=local --reverse origin/$currentBranch..$currentBranch"));
        if (strtotime($pullDate) > strtotime($pushDate)) {
            return $pullDate;
        } else {
            return $pushDate;
        }
    }

    private function getLastMergeDate($currentBranch)
    {
        $output = [];
        $status = null;
        exec('git log --merges -n 1 --format="%ci"', $output, $status);
        if (!$output) {
            return $this->getLastPullPushDate($currentBranch);
        }
        return $status === 0 ? $output[0] : '';
    }

    private function getCurrentBranch()
    {
        $output = [];
        $status = null;
        exec('git rev-parse --abbrev-ref HEAD', $output, $status);
        return $status === 0 ? $output[0] : '';
    }

    private function getLastActionDeveloper($currentBranch)
    {
        $command = "git log -1 --pretty=format:%an $currentBranch";
        $output = trim(exec($command));
        return $output;
    }

    private function getMergedBy()
    {
        $output = [];
        $status = null;
        exec('git log --merges -n 1 --format="%an" HEAD', $output, $status);
        if (!$output) {
            return $this->getLastActionDeveloper('HEAD');
        }
        return $status === 0 ? $output[0] : '';
    }

    private function getDockerPs()
    {
        $output = shell_exec('docker ps');
        //$output = shell_exec('docker ps --format "table {{.Names}}\t\t{{.Image}}\t\t{{.ID}}\t\t{{.Status}}\t\t{{.Ports}}"');
        $data = [];
        if ($output) {
            $lines = explode(PHP_EOL, trim($output));
            $header = array_shift($lines);
            $columns = array_map(function ($col) {
                return preg_replace('/\s+/', '_', strtolower($col));
            }, preg_split('/\s{2,}/', $header));
            foreach ($lines as $line) {
                $row = preg_split('/\s{2,}/', $line);
                if (count($columns) == count($row)) {
                    $item = array_combine($columns, $row);
                    if (!empty($item)) {
                        $data[] = $item;
                    }
                }
            }
        }
        return $data;
    }

    private function getLastCommitMessage()
    {
        $output = shell_exec('git log -n 10 --pretty=format:"%s" | grep -v "CHANGELOG.md"');
        $commit_messages = explode("\n", $output);
        return $commit_messages[0];
    }

    private function getTotalCommits()
    {
        $output = shell_exec('git rev-list --all --count');
        return (int) $output;
    }

}
