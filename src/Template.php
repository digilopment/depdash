<?php

class Template
{

    public $html;

    public function getHtml()
    {
        $this->html = '
            <!doctype html>
            <html>
                <head>
                    <title>Deploy Dashboard</title>
                    <meta name="description" content="Deploy Dashboard">
                    <meta name="keywords" content="Deploy Dashboard">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
                    <script src="/media/js/App.js" data-source="'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'].'/bin.php" defer></script>
                </head>
                <body>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12">
                            </div>
                        </div>
                    </div>
                </body>
            </html>';
        return $this;
    }

    public function render()
    {
        print($this->html);
        return $this;
    }

}
