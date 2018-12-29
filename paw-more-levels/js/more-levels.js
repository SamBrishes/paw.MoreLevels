/*
 |  PawMoreLevels - A small Bludit Hack to go a bit deeper!
 |  @file       ./js/more-levels.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 - Alpha
 |
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - SamBrishes, pytesNET <info@pytes.net>
 */
(function(){
    "use strict";
    var w = window, d = window.document;

    /*
     |  OVERWRITE JS PARENT SLUG
     |  @since  0.1.0
     */
    if(d.querySelector("#jsparentTMP") && d.querySelector("#jskey")){
        var key = d.querySelector("#jskey");
        if(key.value.indexOf("/") !== key.value.lastIndexOf("/")){
            d.querySelector("#jsslug").value = key.value.slice(key.value.lastIndexOf("/")+1);
            d.querySelector("#jsparent").value = key.value.slice(0, key.value.lastIndexOf("/"));
            d.querySelector("#jsparentTMP").value = key.value.slice(0, key.value.lastIndexOf("/"));
        }
    }
}());
