<?php /* 377 Lines */

/**
 *
 * CSS Compiler / Obfuscator / Minifier / Transpiler (PHP 5.3.0 or >)
 *
 * Stylesheet Compiler makes it possible to rename CSS class names 
 * in your stylesheet, which helps reduce the size of the CSS that 
 * is sent down to your users.
 * Of course, this is not particularly useful unless the class names 
 * are renamed consistently in the HTML and CSS files that use the names.
 * Fortunately, you can use the Stylesheet Compiler to update the class 
 * names in your CSS and to update the class names in your HTML.

 * Also it is an unique way to protect your stylesheets from theft. 
 * Actually, it surly doesn't protects sheets from stealing, 
 * because it's not really possible in the web technology.
 *
 * So how it works?
 * This software will transform your stylesheets in a way, 
 * no one will want to modify them. They can steal it, 
 * but who wants to use (and adjust) stylesheet, 
 * which looks like if it's made of the worst coder in the universe?
 *
 * Got it? Try it!
 *
 * Copyright 2020 phpSoftware
 *
 * TODO
 * ALSO PARSE <style></style> TAG IN THE HTML FILE
 *
 */

// SETUP
$delimiter = '--VANGATO--';
$DEBUG     = false; // NEVER SET TO true IN LIVE SYSTEM

// GET POST INPUTS
$htmlFile = '';
if ( isset($_POST['htmlFile']) and !empty($_POST['htmlFile']) and filter_var($_POST['htmlFile'], FILTER_VALIDATE_URL)) {
  $htmlFile = strtok($_POST['htmlFile'], '?');
  $htmlFile = htmlentities($htmlFile);
}

$cssFile = '';
if ( isset($_POST['cssFile']) and !empty($_POST['cssFile']) and filter_var($_POST['cssFile'], FILTER_VALIDATE_URL)) {
  $cssFile = strtok($_POST['cssFile'], '?');
  $cssFile = htmlentities($cssFile);
}

$removeLineBreaks = 'No';
if ( isset($_POST['removeLineBreaks']) && $_POST['removeLineBreaks']=='Yes' ) {
  $removeLineBreaks = 'Yes';
}

// PRINT HEADER
echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"/>
    <title> CSS Compiler / Obfuscator / Minifier / Transpiler </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="data:image/x-icon;base64,AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAgAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAVFFOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABEAABEAAAABEAAAARAAAAEQAAABEAAAARAAAAEQAAABEAAAARAAABEAAAAAEQABEAAAAAABEAEQAAAAAAEQABEAAAAAEQAAARAAAAEQAAABEAAAARAAAAEQAAABEAAAARAAAAEQAAAAEQAAEQAAAAAAAAAAAAD//wAA888AAOfnAADn5wAA5+cAAOfnAADP8wAAn/kAAJ/5AADP8wAA5+cAAOfnAADn5wAA5+cAAPPPAAD//wAA" rel="icon" type="image/x-icon">

    <!-- CLASSLESS CSS FRAMEWORK - https://yegor256.github.io/tacit/ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/yegor256/tacit@gh-pages/tacit-css.min.css">
    
    <!-- SYNTAX LIGHTNING https://highlightjs.org/ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.18.1/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.18.1/highlight.min.js"></script>
    
    <!-- SYNTAX LIGHTNING THEMES -->
    <link rel="stylesheet" href="css-transformer.css">
    
    <!-- SYNTAX LIGHTNING CUSTOME CSS FOR TACIT.CSS -->
    <style>
    body     { padding: 0 5px }
    h2       { margin-top: 10px; font-weight: 700 }
    select   { width: 100%; -webkit-appearance: none; -moz-appearance: none; appearance: none; background:url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'8\' height=\'8\' fill=\'silver\'><polygon points=\'0,0 8,0 4,4\'/></svg>") no-repeat scroll 98% 60% transparent }
    input:not([type="submit"]){ width: 100% }
    pre      { border-left: 0; padding-left: 0; height: 50px; display: block; margin-bottom: 10px; background: #2b2b2b; border-radius: 3.6px }
    pre code { line-height: 20px }
    xmp      { line-height: 1; color: #f8f8f2; padding: 0.5em; word-wrap: break-word }
    footer   { float: right }
    </style>
  </head>
  <body>
    <section>
      <h2> CSS Compiler / Obfuscator / Minifier / Transpiler </h2>
      <form action="" method="POST">
        <fieldset>
          <label><strong>URL TO HTML FILE</strong></label>
          <input size="100" type="text" name="htmlFile" value="' . $htmlFile . '">
          <label><strong>URL TO MATCHING CSS FILE</strong></label>
          <input size="100" type="text" name="cssFile" value="' . $cssFile . '">
          <label><strong>REMOVE LINE BREAKS</strong> (in CSS)</label>
          <select name="removeLineBreaks">
          <option>' . $removeLineBreaks . '</option>
          <optgroup label="Select...">
          <option value="Yes">Yes</option>
          <option value="">No</option>
          </select>
          <input type="submit" name="submit" value="submit">
        </fieldset>
      </form>
      ';

// EXIT HERE IF NO FORM IS SEND
if ( !isset($_POST['submit']) or empty($_POST['htmlFile']) ) exit;

// INI VARs
$chars = 'abcdefghijklmnopqrstuvwxyz';
$i = $z = 0;

// LOAD HTML FILE
$html_content = file_get_contents($htmlFile);

// GET ALL CLASS NAMES FROM THE HTML
preg_match_all('/class="([^\"]+)"/i', $html_content, $html_classes);

// DEBUG HEADER
if ($DEBUG==true) echo '<strong>DEBUG</strong><pre style="height:200px"><xmp>';

// LOOP THROUGH ALL FOUND CLASSES
foreach ($html_classes[1] as $class) {
  if ($DEBUG==true) echo 'HTML CLASS .' . $class . PHP_EOL; // DEV ONLY
  // SPLIT SEVERAL SELECTORS BY SPACE
  $several = explode(' ', $class);
  // CHECK IF THERE WAS SEVERAL SELECTORS
  if (count($several) > 0) {
    // LOOP THROUGH SPLITTED SELECTORS
    foreach ($several as $part) {
      //  ADD THEM ALL
      $classes[$part] = '.' . $part;
    }
  } else {
  // ADD IF ONLY ON SELECTOR WASS IN THE CLASS TAG
    $classes[$class] = '.' . $class;
  }
}

// DEBUG LINE BREAK
if ($DEBUG==true) echo PHP_EOL . PHP_EOL; // DEV ONLY

// GET ALL IDs FROM THE HTML
preg_match_all('/id="([^\"]+)"/i', $html_content, $html_ids);

// LOOP THROUGH ALL FOUND IDs
foreach ($html_ids[1] as $id) {
  if ($DEBUG==true) echo 'HTML ID #' . $id . PHP_EOL; // DEV ONLY
  $ids[$id] = '#' . $id;
}

// ADD CLASSES AND IDs ARRAY TO ONE HTML ARRAY
$html = array_merge($classes, $ids);

// SELECT CSS
if ( !isset($cssFile) or empty($cssFile) ) {
  // GET INLINE CSS
  preg_match_all('/<style(.*)?>(.*)?<\/style>/mi', $html_content, $inlineCSS); // , PREG_SET_ORDER
  #echo '<pre style="height:300px">';var_dump($inlineCSS));echo '</pre>';
  $css_content = implode(PHP_EOL, $inlineCSS[2]);
  $cssFile = '<strong>INLINE FROM HTML</strong>';
} else {
  // LOAD CSS FILE
  $css_content = file_get_contents($cssFile);
}

// GET ALL CLASS NAMES AND IDs FROM THE CSS
#preg_match_all('/^([#|\.][_a-z]+[_a-z0-9-]*)/mi', $css_content, $css_tags);
preg_match_all('/([#|\.][_a-z]+[_a-z0-9-]*) ?{/i', $css_content, $css_tags);

// DEBUG LINE BREAK
if ($DEBUG==true) echo PHP_EOL . PHP_EOL; // DEV ONLY

// LOOP THROUGH ALL FOUND CLASSES AND IDs
foreach ($css_tags[1] as $tag) {
  if ($DEBUG==true) echo 'CSS ' . $tag . PHP_EOL; // DEV ONLY
  // REMOVE FRIST CHAR # AND .
  $index = ltrim($tag, '#.'); 
  // SAVE IIN MULTI ARRAY
  $css[$index] = $tag;
}

// DEBUG FOOTER
if ($DEBUG==true) echo '</xmp></pre>';

// REMOVE DOUBLICATES
$css = array_unique($css);

// HTML RESULT TO BROWSER
echo '<strong>FOUND CLASSES &amp; IDs IN HTML FILE:</strong> <small>' . $htmlFile . '</small>';
echo '<pre><code class="css">';

// LOOP THROUGH ALL MATCHES
foreach ($html as $tag) {
  // PRINT TO BROSER
  echo $tag . PHP_EOL;
}

// CLOSE THE OUTPUT
echo '</code></pre>';

// CSS RESULT TO BROWSER
echo '<strong>FOUND CLASSES &amp; IDs IN THE CSS FILE:</strong> <small>' . $cssFile . '</small>';
if ($cssFile=='<strong>INLINE FROM HTML</strong>') {
  $cssFile = '';
}

// START THE OUTPUT
echo '<pre><code class="css">';

// LOOP THROUGH ALL MATCHES
foreach ($css as $tag) {
  // PRINT TO BROSER
  echo $tag . PHP_EOL;
}

// CLOSE THE OUTPUT
echo '</code></pre>';

// SHOW UNUSED IDs AND CLASS-NAMES IN HTML
echo '<strong>DELETE THIS UNUSED IN HTML</strong> ';
echo '<small>Check that # selectors are not used for ID="" Deeplinks!</small>';
echo '<pre><code class="plaintext">';

// LOOP THROUGH ALL IN HTML FILE FOUND TAGS
$css_found = false;
foreach ($html as $name => $value) {
  // GIVE HINT IF TAG FROM HTML IS NOT FOUND IN CSS THEN DELETE IT IN HTML (IT IS UNUSED IN THE CSS)
  if (!array_key_exists ($name, $css)) {
    echo $value . PHP_EOL;
    $css_found = true;
  } else {
    // BUILD NEW CLASS OR ID NAME
    $new = $chars[$z] . $delimiter . $chars[$i];
    // REPLACE NAMES (CLASS & ID) IN THE CSS
    $css_content = str_replace($value.' ', $value[0].$new, $css_content);
    $css_content = str_replace($value.'{', $value[0].$new.'{', $css_content);
    $css_content = str_replace($value.':', $value[0].$new.':', $css_content);
    $css_content = str_replace($value.',', $value[0].$new.',', $css_content);
    $css_content = str_replace($value."\t", $value[0].$new, $css_content);
    $css_content = str_replace($value."\n", $value[0].$new, $css_content);
    $css_content = str_replace($value."\r", $value[0].$new, $css_content);
    // REPLACE NAMES (CLASS & ID) IN THE HMTL
    $html_content = str_replace('"'.ltrim($value, '#.').'"', '"'.$new.'"', $html_content);   // " "
    $html_content = str_replace('\''.ltrim($value, '#.').'\'', '"'.$new.'"', $html_content); // ' '
    $html_content = str_replace(' '.ltrim($value, '#.').' ', ' '.$new.' ', $html_content);   // SPACE SPACE
    $html_content = str_replace(' '.ltrim($value, '#.').'"', ' '.$new.'"', $html_content);   // SPACE "
    $html_content = str_replace(' '.ltrim($value, '#.').'\'', ' '.$new.'\'', $html_content); // SPACE '
    $html_content = str_replace('"'.ltrim($value, '#.').' ', '"'.$new.' ', $html_content);   // " SPACE
    $html_content = str_replace('\''.ltrim($value, '#.').' ', '\''.$new.' ', $html_content); // ' SPACE
  }
  // INCREMENT FIRST CAHR ON EVERY STEP
  ++$i;
  // AFTER 26 STEPS WE ARE AT "Z" SO GO BACK TO "A" (RESET INCREMENT OF $1)
  if ($i == 26) {
    $i = 0;
    // ALSO INCREMENT $z SO WE CAN HAVE THE NEXT CHAR AT THE SECOND POSITION
    ++$z;
  }
}

// IF EMPTY PRINt HINT
if ($css_found!=true) {
  echo '👍 nothing found';
}

// CLOSE THE OUTPUT
echo '</code></pre>';

// SHOW UNUSED IDs AND CLASS-NAMES IN CSS
echo '<strong>DELETE THIS UNUSED IN CSS</strong>';
echo '<pre><code class="plaintext">';

// LOOP THROUGH ALL IN CSS FOUND TAGS
$html_found = false;
foreach ($css as $name => $value) {
  if ($DEBUG==true) echo 'USED ' . $name . ' -- ' . $value . PHP_EOL;
  // GIVE HINT IF TAG FROM CSS IS NOT FOUND IN HTML THEN DELETE IT IN CSS (IT IS UNUSED IN THE HTML)
  if (!array_key_exists ($name, $html)) {
    echo $value . PHP_EOL;
    $html_found = true;
  }
}

// IF EMPTY PRINt HINT
if ($html_found!=true) {
  echo '👍 nothing found';
}

// CLOSE THE OUTPUT
echo '</code></pre>';

// MINIMIZE THE CSS
$css_content = minimizeCSSsimple($css_content, $removeLineBreaks);

// SHOW CSS RESULT TO BROWSER
echo '<strong>RESULT OF CSS</strong>';
echo '<pre><code class="css">';

// PRINT TO BROWSER
echo $css_content;

// CLOSE THE OUTPUT
echo '</code></pre>';

// SET IN THE NEW CSS FILE
if (!empty($cssFile)) {
  $html_content = str_replace('"'.basename($cssFile).'"', '"css.css"', $html_content);
  // WRITE THE NEW FILE TO FILESYSTEM
  file_put_contents('css.css', $css_content);
} else {
  $html_content = preg_replace('/<style(.*)?>(.*)?<\/style>/', '<style>'.$css_content.'</style>', $html_content);
}

// MINIMIZE THE HTML
$html_content = minimizeHTMLsimple($html_content, $removeLineBreaks);

// SHOW HTML RESULT TO BROWSER
echo '<strong>RESULT OF HTML</strong>';
echo '<pre style="height:200px;border-radius: 3.6px; border: 3px solid #2b2b2b;">';

// XMP WITHOUT OR WITH LINE BRAKES
if ($removeLineBreaks=='Yes') {
  echo '<xmp class="html" style="white-space: normal;">';
} else {
  echo '<xmp class="html">';
}

// PRINT TO BROWSER
echo $html_content;

// CLOSE THE OUTPUT
echo '</xmp></pre>';

// WRITE THE NEW FILE TO FILESYSTEM
file_put_contents('htm.htm', $html_content);

// HELPER FUNCTIONS
function minimizeCSSsimple($css, $removeLineBreaks='') {
  $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
  $css = preg_replace('/\s{2,}/', ' ', $css);
  $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
  $css = preg_replace('/;\s?}/', '}', $css);
  if ($removeLineBreaks=='Yes') $css = preg_replace( "/\r|\n/", '', $css); // REMOVE LINE BREAKS
  return trim($css);
}

function minimizeHTMLsimple($html, $removeLineBreaks='') {
  $html = preg_replace('/<!--(.|\s)*?-->/', '', $html); // REMOVE HTML COMMENTS - https://davidwalsh.name/remove-html-comments-php
  $html = preg_replace('/^[ \t]*/m', '', $html); // REMOVE LEADING WHITESPACE (SPACES OR TABS) - https://stackoverflow.com/a/34322250
  $html = preg_replace('/[ \t]*$/m', '', $html); // REMOVE ENDING WHITESPACE (SPACES OR TABS)
  $html = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $html); // REMOVE EMPTY LINES - https://stackoverflow.com/a/709684
  if ($removeLineBreaks=='Yes')  $html = preg_replace( "/\>[\r|\n]/", '>', $html); // REMOVE LINE BREAKS
  return trim($html);
}

// PRINT FOOTER TO BROWSER
echo '
      <footer>
        <p><small>Copright '.date('Y').' <a href="https://www.adilbo.com/">www.adilbo.com</a></small></p>
      </footer>
    </section>
      <script>
      document.querySelectorAll("code").forEach(function(element) {
        element.innerHTML = element.innerHTML.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/\'/g, "&#039;");
      });
      hljs.tabReplace = "    ";
      hljs.initHighlightingOnLoad();
    </script>
  </body>
</html>
';

/* EOF - END OF FILE */
