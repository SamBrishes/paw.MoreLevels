<?php
/*
 |  MoreLevels  A small hacky way to get more levels on your content.
 |  @file       ./plugin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/more-levels
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

    // Main Plugin Class
    class PawMoreLevels extends Plugin {
        /*
         |  CONSTRUCTOR
         |  @since  0.1.1
         */
        public function __construct() {
            parent::__construct();

            // Check if installed
            if(!$this->installed()) {
                return null;
            }
            global $pages;

            // Overwrite Pages Class
            require_once "system" . DS . "pages.class.php";
            $pages = new PawPages();

            // Check for AJAX
            if(strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "") === "xmlhttprequest") {
                $this->overwriteAJAX();
            }
        }

        /*
         |  RESPONSE :: CHECK AJAX REQUEST
         |  @since  0.2.0
         */
        protected function overwriteAJAX() {
            global $url;
            global $pages;

            // Check Version
            $slug = $url->explodeSlug();
            if(version_compare(BLUDIT_VERSION, "3.10.0", "<")) {
                $call = $slug[0] === "ajax" && $slug[1] === "get-parents";
            } else {
                $call = $slug[0] === "ajax" && $slug[1] === "get-published";
            }
            if(!$call) {
                return false;
            }

            // Check Referrer
            if(strpos($_SERVER["HTTP_REFERER"], "/new-content") === false && strpos($_SERVER["HTTP_REFERER"], "/edit-content") === false) {
                return false;
            }

            // Handle depending on version
            if(version_compare(BLUDIT_VERSION, "3.10.0", "<")) {
                return $this->response();
            }
            return $this->response310();
        }

        /*
         |  RESPONSE :: BLUDIT < 3.10.0
         |  @since  0.2.0
         */
        protected function response() {
            global $pages;

            // Get Current
            if(strpos($_SERVER["HTTP_REFERER"], "/edit-content") !== false){
                $current = explode("edit-content/", $_SERVER["HTTP_REFERER"])[1];
            }

            // Get Query
            $query = isset($_GET["query"])? Text::lowercase($_GET["query"]): false;
            if($query === false) {
                header("Content-Type: application/json");
                die(json_encode(array("status" => 1, "files" => "Invalid query.")));
            }

            // Get Pages
            $temp = array();
            foreach($pages->getDB() AS $pageKey) {
                $page = new Page($pageKey);
                if(Text::stringContains(Text::lowercase($page->title()), $query)) {
                    $temp[$page->key()] = $page->title();

                    if(substr_count($page->key(), "/") > 0) {
                        $path = explode("/", $page->key());
                        $title = array();

                        while(count($path) > 1) {
                            $getPage = new Page($path[0]);
                            $title[] = $getPage->title();
                            $path[0] = array_shift($path) . "/" . $path[0];
                        }
                        if(isset($current) && strtolower($page->key()) === strtolower($current)) {
                            continue;
                        }

                        $temp[$page->key()] = implode(" / ", $title) . " / " . $temp[$page->key()];
                    }
                }
            }

            // Overwrite
            header("Content-Type: application/json");
            die(json_encode(array_flip($temp)));
        }

        /*
         |  RESPONSE :: BLUDIT >= 3.10.0
         |  @since  0.2.0
         */
        protected function response310() {
            global $pages;

            // Get Current
            if(strpos($_SERVER["HTTP_REFERER"], "/edit-content") !== false){
                $current = explode("edit-content/", $_SERVER["HTTP_REFERER"])[1];
            }

            // Get Query
            $query = empty($_GET["query"])? false: Text::lowercase($_GET["query"]);
            if($query === false) {
                header("Content-Type: application/json");
                return ajaxResponse(1, "Invalid query.");
            }

            // Get Result
            $result = array();
            $pagesKey = $pages->getDB();
            foreach($pagesKey as $pageKey) {
            	try {
            		$page = new Page($pageKey);

                    // Skip Current
                    if(isset($current) && strtolower($page->key()) === strtolower($current)) {
                        continue;
                    }

                    // Check Query
        			if($page->published() || $page->sticky() || $page->isStatic()) {
        				$lowerTitle = Text::lowercase($page->title());
        				if(Text::stringContains($lowerTitle, $query)) {
        					$tmp = array('disabled'=>false);
        					$tmp['id'] = $page->key();
        					$tmp['text'] = $page->title();
        					$tmp['type'] = $page->type();
        					array_push($result, $tmp);
        				}
        			}
            	} catch (Exception $e) {
            		// continue
            	}
            }

            // Overwrite
            header("Content-Type: application/json");
            die(json_encode(array("results" => $result)));
        }

        /*
         |  HOOK :: ADMIN BODY END
         |  @since  0.1.0
         */
        public function adminBodyEnd() {
            global $url;
            global $page;

            // Check URL
            if(strpos($url->slug(), "new-content") !== 0 && strpos($url->slug(), "edit-content") !== 0) {
                return null;
            }

            // Prepare Parent
            if(!empty($page)) {
                $parent = explode("/", $page->key());
                $parent = new Page(substr($page->key(), 0, strrpos($page->key(), "/")));
            }

            // Render JavaScript
            ob_start();
            ?>
                <script type="text/javascript">
                    "use strict";

                    /*
                     |  OVERWRITE PARENT SLUG (BLUDIT < 3.10.0)
                     |  @since  0.1.0
                     */
                    if(document.querySelector("#jsparentTMP") && document.querySelector("#jskey")) {
                        var key = document.querySelector("#jskey");
                        if(key.value.indexOf("/") !== key.value.lastIndexOf("/")){
                            document.querySelector("#jsslug").value = key.value.slice(key.value.lastIndexOf("/")+1);
                            document.querySelector("#jsparent").value = key.value.slice(0, key.value.lastIndexOf("/"));
                            document.querySelector("#jsparentTMP").value = key.value.slice(0, key.value.lastIndexOf("/"));
                        }
                    }

                    /*
                     |  OVERWRITE PARENT SLUG (BLUDIT >= 3.10.0)
                     |  @since  0.2.0
                     */
                    if(document.querySelector("#jsparent") && document.querySelector("#jsparent").options.length > 0) {
                        var select = document.querySelector("#jsparent");
                        select.options[0].setAttribute("value", "<?php echo $parent->key(); ?>");
                        select.options[0].innerText = "<?php echo str_replace("\"", "", $parent->title()); ?>";
                    }
                </script>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
