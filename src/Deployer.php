<?php

class Deployer
{

    private $folders;
    private $customTagOrBranch;
    private $config;

    public function __construct(array $repositories)
    {
        $this->config = (new Config())->init();
        $folders = [];
        foreach ($repositories as $folder) {
            $folders[] = $this->config['getRepositoriesPath'] . $folder;
        }
        $this->folders = $folders;
    }

    public function deploy(string $customTagOrBranch = null)
    {
        $this->customTagOrBranch = $customTagOrBranch;

        foreach ($this->folders as $folder) {

            chdir($folder);
            $outputFetchAll = $this->taskFetchAll();
            $latestTagOutput = $this->taskGetLatestTag();
            $latestTag = trim($latestTagOutput);

            if (!empty($this->customTagOrBranch)) {
                $tagOrBranch = $this->customTagOrBranch;
            } elseif (!empty($latestTag)) {
                $tagOrBranch = $latestTag;
            } else {
                $tagOrBranch = 'master';
            }

            echo "Deployed folder: $folder\n";
            echo "Git fetch output:\n";
            echo $outputFetchAll . "\n";
            echo "Tag/branch deployed: $tagOrBranch\n";
            echo "Git checkout output:\n";
            echo $this->taskCheckout($tagOrBranch) . "\n";
            echo "Composer output:\n";
            echo $this->taskComposerInstall($folder) . "\n";
        }
    }

    private function taskFetchAll()
    {
        return shell_exec('git fetch --all');
    }

    private function taskGetLatestTag()
    {
        return shell_exec('git describe --tags $(git rev-list --tags --max-count=1)');
    }

    private function taskCheckout($tagOrBranch)
    {
        return shell_exec("git checkout $tagOrBranch");
    }

    private function taskComposerInstall($folder)
    {
        if (file_exists($folder . '/composer.lock')) {
            return shell_exec("composer install");
        }
        return 'No composer found';
    }

    public function getFolders(): array
    {
        return $this->folders;
    }

    public function setFolders(array $folders): void
    {
        $this->folders = $folders;
    }

    public function getCustomTagOrBranch(): ?string
    {
        return $this->customTagOrBranch;
    }

    public function setCustomTagOrBranch(?string $customTagOrBranch): void
    {
        $this->customTagOrBranch = $customTagOrBranch;
    }

}
