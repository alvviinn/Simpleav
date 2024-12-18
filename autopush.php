<?php


class AutoPush
{
    
    /**
     * Directory for which the autopush should 
     * look for changes in the files in it
     */
    public $directory = __DIR__;

    /**
     * Set the interval for which autopush 
     * should evaluating the code for pushing
     * 
     */
    public $interval = 1;
    
    

    public $previous = [];

    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->previous = $this->getFileModificationTimes();
    }
    
    /**
     * Listens for changes awaiting after a 
     * certain interval of time
     * 
     */
    public function listen()
    {
        $this->log($this->style("Listening for changes in directory: {$this->directory}",self::FG_GREEN));
        $this->log($this->spinner("listening"));
        while (true) {
            sleep($this->interval);
            if (!$this->onChange()) continue;

            echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
            $this->push();
        }
    }

    /**
     * handles the changes that have been detected
     * 
     * @return bool
     */
    public function onChange()
    {
        $current = $this->getFileModificationTimes();
        if ($this->previous == $current) return false;

        $this->previous = $current;
        $this->log($this->spinner("listening"));
        return true;
    }

    /**
     * Keeps track of the time all the files that were changed
     * 
     *
     * @return array 
     */
    private function getFileModificationTimes()
    {
        $modTimes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $modTimes[$filePath] = filemtime($filePath);
            }
        }

        return $modTimes;
    }

    /**
     * Pushes the changes to git 
     * 
     *   @return object
     *   
     * */
    public function push()
    {
        $changes = $this->getChanges();
        $branch = $this->getBranch();
        $message = $this->generateCommitMessage($changes);

        $escapedMessage = escapeshellarg($message);

        $this->log(shell_exec("git add *"));
        $this->log(shell_exec("git commit -m '$escapedMessage'")); 
        $this->log(shell_exec("git push origin '$branch'"));
        return $this;
    }

    /**
     * Retrieve the changes between the working directory and the last commit.
     */
    private function getChanges()
    {
        return shell_exec("git diff --staged");
    }
    
    /**
     * Get the branch that the user is in
     * 
     */
    private function getBranch(){
        return shell_exec('git branch --show-current');
    }



    /**
     * Use Gemini to generate a meaningful commit message based on changes.
     *
     * 
     * @param string $changes The code changes to analyze.
     * @return string The generated commit message (or empty string on error).
     */
    private function generateCommitMessage($changes)
    {
        $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyDw7MZCANOn3F8mrY1Kep2D49LPwHeZBNA';

        $fileChanges = shell_exec("git status --short");

       
        $purpose = $this->detectPurpose($fileChanges, $changes);

        
        $prompt = "Generate a concise Git commit message for the following changes:\n\n"
            . "Files changed:\n$fileChanges\n"
            . "Summary of changes: $purpose\n\n"
            . "Detailed changes:\n$changes";

        $data = array(
            "contents" => array(
                "parts" => array(
                    array(
                        "text" => $prompt
                    )
                )
            )

        );

        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {

            return '';
        }

        $result = json_decode($response, true);


        return $result["candidates"][0]["content"]["parts"][0]["text"];
    }

    /** 
     * Detects the purposed based on the 
     * types of file changes that
     * occured     * 
     * @param $fileChanges
     * 
     * 
     * @return string
     */
    private function detectPurpose($fileChanges)
    {
        
        if (stripos($fileChanges, 'add') !== false) {
            return "Add new features or files.";
        } elseif (stripos($fileChanges, 'delete') !== false) {
            return "Remove files or functionality.";
        } elseif (stripos($fileChanges, 'modify') !== false) {
            return "Modify existing code.";
        } else {
            return "General code changes.";
        }
    }

    /**
     * log something to the console
     */
    private function log($message)
    {
       $signature = $this->style("Autopush🚀", self::FG_CYAN);

        echo "$signature: $message \n\n";
        return $this;
    }

    /**
     * ascii colors
     */
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
    const UNDERLINE = "\033[4m";
    const FG_RED = "\033[31m";
    const FG_GREEN = "\033[32m";
    const FG_YELLOW = "\033[33m";
    const FG_BLUE = "\033[34m";
    const FG_CYAN = "\033[36m";
    const BG_RED = "\033[41m";
    const BG_GREEN = "\033[42m";
    const BG_YELLOW = "\033[43m";
    const BG_BLUE = "\033[44m";

   
    function style($text, $color = '', $bg = '', $format = '') {
        return $format . $color . $bg . $text . self::RESET;
    }
    /**
     * 
     *  @param {*} $message - The message that will be shown as the spinner is running
     *  @param {*} $duration - The duration for which the spinner should go for
     * 
     */
    public function spinner($message, $duration = null) {
        $duration = $duration ?? $this->interval;
        
        $spinnerChars = ['|', '/', '-', '\\'];
        $spinnerCount = count($spinnerChars);

       
        $startTime = time();

       
        echo self::style($message, self::FG_YELLOW) . " ";

        
        while ((time() - $startTime) < $duration) {
            for ($i = 0; $i < $spinnerCount; $i++) {
                echo "\033[D" . $spinnerChars[$i];  
                usleep(100000); 
            }
        }

        
        echo "\r\033[K" . self::style("✓ Done!", self::FG_GREEN) . PHP_EOL;
    }
}

$autoPush = new AutoPush(__DIR__);
$autoPush->listen();
