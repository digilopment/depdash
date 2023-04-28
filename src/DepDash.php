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
            chdir($path); // change directory to git repository

            $repositoryUrl = $this->getRepositoryUrl();
            $mergeVersion = $this->getMergeVersion();

            $environmentStatuses = $this->getEnvironmentStatuses($mergeVersion, $repositoryUrl, $subdir);

            $newName = str_replace($this->config['projectRename']['default'], $this->config['projectRename']['new'], $subdir);
            $projectName = str_replace('-', ' ', ucwords($newName));
            $deploymentProject = [
                'deploymentProject' => ['name' => $this->config['projectPrefix'] . '' . $projectName . '' . $this->config['projectSuffiks']],
                'environmentStatuses' => $environmentStatuses
            ];

            $results[] = $deploymentProject;
        }
        $this->results = $results;
        return $this;
    }

    public function withJson()
    {
        $this->results = json_encode($this->results);
        print($this->results);
        return $this;
    }

    public function writeToFile()
    {
        file_put_contents(__DIR__ . '/../public/json/data.json', $this->results);
        return $this;
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

                $mergedBy = $this->getMergedBy($currentBranch);
                if ($mergedBy) {
                    $repoResults['reasonSummary'] = 'Manual run by ' . $mergedBy;
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
        return array_diff(scandir($this->dir), array_merge(['..', '.'], $this->config['projectExclude']));
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

        //return exec("git config --get remote.origin.url | sed 's/\(.*\/\/\)\([^@]*@\)\?\([^\/]*\)/\1\3/'");
    }

    private function getMergeVersion()
    {
        return exec('git rev-list --count --merges master');
    }

    private function getLastPullDate($currentBranch)
    {
        $output = [];
        $status = null;
        exec('git log --remotes -n 1 --format="%ci" ' . $currentBranch, $output, $status);
        return $status === 0 ? $output[0] : '';
    }

    private function getLastMergeDate($currentBranch)
    {
        $output = [];
        $status = null;
        exec('git log --merges -n 1 --format="%ci"', $output, $status);
        return $status === 0 ? $output[0] : '';
    }

    /* private function getLastMergeDate($currentBranch)
      {
      $output = exec('git log --merges -n 1 --format="%ci"');
      return $output === 0 ? '' : $output;
      }
     */

    private function getCurrentBranch()
    {
        $output = [];
        $status = null;
        exec('git rev-parse --abbrev-ref HEAD', $output, $status);
        return $status === 0 ? $output[0] : '';
    }

    private function getMergedBy($currentBranch)
    {
        $output = [];
        $status = null;
        exec('git log --merges -n 1 --format="%an" ' . $currentBranch, $output, $status);
        return $status === 0 ? $output[0] : '';
    }

}
