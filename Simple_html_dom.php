<?php
define('HDOM_TYPE_ELEMENT', 1);define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT', 3);define('HDOM_TYPE_ENDTAG', 4);
define('HDOM_TYPE_ROOT', 5);define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO', 3);define('HDOM_INFO_BEGIN', 0);
define('HDOM_INFO_END', 1);define('HDOM_INFO_QUOTE', 2);
define('HDOM_INFO_SPACE', 3);define('HDOM_INFO_TEXT', 4);
define('HDOM_INFO_INNER', 5);define('HDOM_INFO_OUTER', 6);
define('HDOM_INFO_ENDSPACE', 7);
defined('DEFAULT_TARGET_CHARSET') || define('DEFAULT_TARGET_CHARSET',
'UTF-8');
defined('DEFAULT_BR_TEXT') || define('DEFAULT_BR_TEXT', "\r\n");
defined('DEFAULT_SPAN_TEXT') || define('DEFAULT_SPAN_TEXT', ' '); 
defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 600000);
define('HDOM_SMARTY_AS_TEXT', 1); function file_get_html(
$url,$use_include_path = false,$context = null,$offset = 0,$maxLen = -1,$lowercase = true,
$forceTagsClosed = true,$target_charset = DEFAULT_TARGET_CHARSET,$stripRN = true,
$defaultBRText = DEFAULT_BR_TEXT,$defaultSpanText = DEFAULT_SPAN_TEXT)
{ if($maxLen <= 0) { $maxLen = MAX_FILE_SIZE; }
$dom = new simple_html_dom
(null,$lowercase,$forceTagsClosed,$target_charset,$stripRN,$defaultBRText,
$defaultSpanText;
if (empty($contents) || strlen($contents) > $maxLen) {$dom->clear();return false;}
return $dom->load($contents, $lowercase, $stripRN);} function str_get_html(
 $str, $lowercase = true,$forceTagsClosed = true,
 $target_charset = DEFAULT_TARGET_CHARSET,$stripRN = true,
 $defaultBRText = DEFAULT_BR_TEXT,$defaultSpanText = DEFAULT_SPAN_TEXT){
 $dom = new simple_html_dom(
 null,$lowercase,$forceTagsClosed,$target_charset,$stripRN,$defaultBRText,
$defaultSpanText);
 return $dom->load($str, $lowercase, $stripRN);} function
dump_html_tree($node, $show_attr = true, $deep = 0){
 $node->dump($node);} class
simple_html_dom_node{
 public $nodetype = HDOM_TYPE_TEXT; public $tag = 'text';
public $attr = array(); public $children = array(); public $nodes
= array(); public $parent = null;
public $_ = array(); public $tag_start = 0; private $dom = null;
function __construct($dom){
 $this->dom = $dom; $dom->nodes[] = $this;}
function dump($show_attr = true, $depth = 0){
echo str_repeat("\t", $depth) . $this->tag; if
($show_attr && count($this->attr) > 0) { 
  }echo "\n";}
 function dump_node($echo = true){
 $string = $this->tag;
if (count($this->_) > 0) {
$string .= ' $_ (';
 foreach ($this->_ as $k => $v) {
if (is_array($v)) { $string .=
"[$k]=>("; foreach ($v as
$k2 => $v2) {
 $string .= "[$k2]=>\"$v2\", ";}$string .= ')';} else {$string .= "[$k]=>\"$v\", ";}}
$string .= ')';}
 $string .= ' HDOM_INNER_INFO: '; if
(isset($node->_[HDOM_INFO_INNER])) {
 $string .= "'" . $node->_[HDOM_INFO_INNER] . "'";
 } else {$string .= ' NULL ';}
 $string .= ' children: ' . count($this->children);
 $string .= ' nodes: ' . count($this->nodes);
 $string .= ' tag_start: ' . $this->tag_start;
 $string .= "\n";
 if ($echo) { echo $string;return;}
function prev_sibling(){ if ($this-
>parent === null) { return null;
}
$idx = array_search($this, $this->parent->children,
true); if ($idx !== false && $idx > 0) { return $this-
>parent->children[$idx - 1];
 }
 return null;
 } 
  if (isset($this->_[HDOM_INFO_OUTER])) {
return $this->_[HDOM_INFO_OUTER];
 }
 if (isset($this->_[HDOM_INFO_TEXT])) {
 return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
 }
 $ret = '';
 if ($this->dom && $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]) {
 $ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();
 }
 if (isset($this->_[HDOM_INFO_INNER])) {
 if ($this->tag !== 'br') {
 $ret .= $this->_[HDOM_INFO_INNER];
 }
 } elseif ($this->nodes)
{ foreach ($this->nodes as $n)
{
 $ret .= $this->convert_text($n->outertext());
 }
}
if (isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END] != 0) {
$ret .= '</' . $this->tag . '>';
}
return $ret;
}
 if (strcasecmp($this->tag, 'script') === 0) { return ''; }
if (strcasecmp($this->tag, 'style') === 0) { return ''; }
 $ret = '';
if (!is_null($this->nodes)) {
foreach ($this->nodes as $n) {
if ($n->tag === 'p') {
 $ret = trim($ret) . "\n\n";
 }
 $ret .= $this->convert_text($n->text());
if ($n->tag === 'span') {
 $ret .= $this->dom->default_span_text;
 }}}
return $ret;
 }
 function xmltext(){
 $ret = $this->innertext();
 $ret = str_ireplace('<![CDATA[', '', $ret);
$ret = str_replace(']]>', '', $ret); return
$ret;
 }
 function makeup(){
 if (isset($this->_[HDOM_INFO_TEXT])) { return $this->dom-
>restore_noise($this->_[HDOM_INFO_TEXT]);
}
$ret = '<' . $this->tag;
$i = -1;
foreach ($this->attr as $key => $val) {
 ++$i;
 if ($val === null || $val === false) { continue; }
 $ret .= $this->_[HDOM_INFO_SPACE][$i][0];
 if ($val === true) {
 $ret .= $key;
 } else { 
  switch ($this->_[HDOM_INFO_QUOTE][$i]){
case HDOM_QUOTE_DOUBLE: $quote = '"'; break;
case HDOM_QUOTE_SINGLE: $quote = '\''; break;
 default: $quote = '';
 }
 $ret .= $key
 . $this->_[HDOM_INFO_SPACE][$i][1]
 . '='
 . $this->_[HDOM_INFO_SPACE][$i][2]
 . $quote. $val. $quote;}}
 $ret = $this->dom->restore_noise($ret); return $ret .
$this->_[HDOM_INFO_ENDSPACE] . '>';} function
__get($name){ if (isset($this->attr[$name])) {
 return $this->convert_text($this->attr[$name]);
 }
 switch ($name) { case 'outertext': return
$this->outertext(); case 'innertext': return
$this->innertext();
case 'plaintext': return $this->text();
 case 'xmltext': return $this->xmltext(); default:
return array_key_exists($name, $this->attr);
 } }
function __set($name, $value)
 {
 global $debug_object;
 if (is_object($debug_object)) { $debug_object->debug_log_entry(1); }
switch ($name) {
 case 'outertext': return $this->_[HDOM_INFO_OUTER] = $value;
case 'innertext': 
  if (isset($this->_[HDOM_INFO_TEXT])) {
return $this->_[HDOM_INFO_TEXT] = $value;
 }
 return $this->_[HDOM_INFO_INNER] = $value;
 }
 if (!isset($this->attr[$name])) {
 $this->_[HDOM_INFO_SPACE][] = array(' ', '', '');
 $this->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
 }
 $this->attr[$name] = $value;}
function __isset($name)
{ switch ($name)
{ case 'outertext': return
true; case 'innertext': return
true; case 'plaintext': return true;
}
return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
}
function __unset($name){
 if (isset($this->attr[$name])) { unset($this->attr[$name]); }
}
function convert_text($text){
 global $debug_object;
 if (is_object($debug_object)) { $debug_object->debug_log_entry(1); }
 $converted_text = $text;
 $sourceCharset = '';
$targetCharset = ''; if
($this->dom) {
 $sourceCharset = strtoupper($this->dom->_charset);
 $targetCharset = strtoupper($this->dom->_target_charset); 
  }
 if (is_object($debug_object)) {
 $debug_object->debug_log(3,
 'source charset: '
 . $sourceCharset
 . ' target charaset: '
 . $targetCharset
 );
 }
 if (!empty($sourceCharset)
 && !empty($targetCharset)
 && (strcasecmp($sourceCharset, $targetCharset) != 0))
{ if ((strcasecmp($targetCharset, 'UTF-8') == 0)
 && ($this->is_utf8($text))) {
 $converted_text = $text;
 } else {
 $converted_text = iconv($sourceCharset, $targetCharset, $text);
 }
 }
 if ($targetCharset === 'UTF-8') {
 if (substr($converted_text, 0, 3) === "\xef\xbb\xbf") {
 $converted_text = substr($converted_text, 3);
 }
 if (substr($converted_text, -3) === "\xef\xbb\xbf") {
 $converted_text = substr($converted_text, 0, -3);
 }
 }
 return $converted_text;
 } 
static function is_utf8($str){$c = 0; $b = 0;$bits = 0;$len = strlen($str);
for($i = 0; $i < $len; $i++) {$c = ord($str[$i]);
 if($c > 128) {if(($c >= 254)) { return false; }elseif($c >= 252) { $bits = 6; }
elseif($c >= 248) { $bits = 5; } elseif($c >= 240) { $bits = 4; }
elseif($c >= 224) { $bits = 3; } elseif($c >= 192) { $bits = 2; }
else { return false; }
 if(($i + $bits) > $len) { return false; }
}}
 return true;
}
function get_display_size(){
global $debug_object;
$width = -1; $height =
-1; if ($this->tag !==
'img') { return false;}
 if (isset($this->attr['width'])) {$width = $this->attr['width'];}
 if (isset($this->attr['height'])) {$height = $this->attr['height'];}
if (isset($this->attr['style'])) {$attributes = array();
preg_match_all('/([\w-]+)\s*:\s*([^;]+)\s*;?/',
$this->attr['style'],$matches,PREG_SET_ORDER);
foreach ($matches as $match) {
 $attributes[$match[1]] = $match[2];
 }
 if (isset($attributes['width']) && $width == -1) { if
(strtolower(substr($attributes['width'], -2)) === 'px')
{ $proposed_width = substr($attributes['width'], 0, -2);
if (filter_var($proposed_width, FILTER_VALIDATE_INT)) {
 $width = $proposed_width;
 }}} 
  if (isset($attributes['height']) && $height == -1) { if
(strtolower(substr($attributes['height'], -2)) == 'px')
{ $proposed_height = substr($attributes['height'], 0, -2);
if (filter_var($proposed_height, FILTER_VALIDATE_INT)) {
 $height = $proposed_height;
 }}}}
 $result = array(
 'height' => $height,
 'width' => $width
);
return $result;
}
function save($filepath = ''){
$ret = $this->outertext();
if ($filepath !== '') {
 file_put_contents($filepath, $ret, LOCK_EX);
 }
return $ret;
 }
 function addClass($class){
if (is_string($class)) {
 $class = explode(' ', $class);
 }
 if (is_array($class))
{ foreach($class as $c) {
if (isset($this->class))
{ if ($this->hasClass($c))
{ continue;
 } else {
 $this->class .= ' ' . $c; 
 }
 } else {
 $this->class = $c;
 }}
 } else { if
(is_object($debug_object)) {
 $debug_object->debug_log(2, 'Invalid type: ', gettype($class));
}}}
function hasClass($class){
if (is_string($class))
{ if (isset($this-
>class)) {
 return in_array($class, explode(' ', $this->class), true);
 }
 } else {
 if (is_object($debug_object)) {
 $debug_object->debug_log(2, 'Invalid type: ', gettype($class));
 }}
return false;
 }
 function removeClass($class = null){
if (!isset($this->class)) { return;
 }
 if (is_null($class)) {
 $this->removeAttribute('class');
return;
 }
 if (is_string($class)) {
 $class = explode(' ', $class);
 } 
if (is_array($class)) {
 $class = array_diff(explode(' ', $this->class), $class);
if (empty($class)) {
 $this->removeAttribute('class');
 } else {
 $this->class = implode(' ', $class);}}}
function hasAttribute($name){return $this->__isset($name);} function
removeAttribute($name){$this->__set($name, null);} function remove(){if
($this->parent) {$this->parent->removeChild($this);}} function
removeChild($node){
 $nidx = array_search($node, $this->nodes, true);
 $cidx = array_search($node, $this->children, true);
$didx = array_search($node, $this->dom->nodes, true); if
($nidx !== false && $cidx !== false && $didx !== false) {
foreach($node->children as $child) {
 $node->removeChild($child);
 }
 foreach($node->nodes as $entity) {
 $enidx = array_search($entity, $node->nodes, true);
$edidx = array_search($entity, $node->dom->nodes, true);
if ($enidx !== false && $edidx !== false)
{ unset($node->nodes[$enidx]);
unset($node->dom->nodes[$edidx]);
 }}
 unset($this->nodes[$nidx]); unset($this-
>children[$cidx]); unset($this->dom->nodes[$didx]);
 $node->clear();
 }}
 function getElementById($id){return $this->find("#$id", 0); }
 function getElementsById($id, $idx = null){return $this->find("#$id", $idx); } 
function getElementByTagName($name){ return $this->find($name, 0); }
function getElementsByTagName($name, $idx = null){ return $this->find($name, $idx); }
function parentNode(){return $this->parent(); }
function childNodes($idx = -1){ return $this->children($idx); }
function firstChild(){ return $this->first_child(); }
 function lastChild(){return $this->last_child();}
function nextSibling(){return $this->next_sibling();}
function previousSibling(){return $this->prev_sibling();}
function hasChildNodes(){return $this->has_child();}
function appendChild($node){ $node->parent($this);
return $node;
 }}
class simple_html_dom{
public $root = null;public $nodes = array();public $callback = null;public $lowercase = false;
public $original_size;public $size;protected $pos;protected $doc;protected $char;
protected $cursor;protected $parent;protected $noise = array();protected $token_blank = " \t\r\n";
protected $self_closing_tags = array(
'area' => 1,'base' => 1,'br' => 1,'col' => 1,'embed' => 1,'hr' => 1,'img' => 1,'input' => 1, 'link'
=> 1,'meta' => 1,'param' => 1,'source' => 1,'track' => 1,'wbr' => 1 );
 }}
 if (!$forceTagsClosed) {
 $this->optional_closing_array = array();
 }
 $this->_target_charset = $target_charset;
 }
 function __destruct(){$this->clear();}
function load_file(){ $args =
func_get_args();
if(($doc = call_user_func_array('file_get_contents', $args)) !== false) {
$this->load($doc, true); 
                     } else {return false;}} function
set_callback($function_name){
 $this->callback = $function_name;
 }
 function remove_callback(){$this->callback = null;}
function save($filepath = '') $ret = $this->root-
>innertext();
 if ($filepath !== '') { file_put_contents($filepath, $ret, LOCK_EX); }
return $ret;
 }
 function find($selector, $idx = null, $lowercase = false)
{ return $this->root->find($selector, $idx, $lowercase);
 } function clear(){ if
(isset($this->nodes))
{ foreach ($this->nodes as $n)
{
 $n->clear();
 $n = null;
 }}
 if (isset($this->children))
{ foreach ($this->children as $n)
{
 $n->clear();
 $n = null;
 }}
 if (isset($this->parent)) { $this->parent-
>clear(); unset($this->parent);
}}
if (!preg_match('/^\w[\w:-]*$/', $tag)) {
  $node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until('<>');
if ($this->char === '<') {
 $this->link_nodes($node, false);
return true;
 }
 if ($this->char === '>') { $node->_[HDOM_INFO_TEXT] .= '>'; }
 $this->link_nodes($node, false);
 $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
return true;
 }
 $node->nodetype = HDOM_TYPE_ELEMENT;
 $tag_lower = strtolower($tag);
 $node->tag = ($this->lowercase) ? $tag_lower : $tag;
if (isset($this->optional_closing_tags[$tag_lower])) {
 while (isset($this->optional_closing_tags[$tag_lower][strtolower($this->parent->tag)])) {
 $this->parent->_[HDOM_INFO_END] = 0;
 $this->parent = $this->parent->parent;
 }
 $node->parent = $this->parent;
 }
 $guard = 0; // prevent infinity loop
 $space = array($this->copy_skip($this->token_blank), '', '');
do {
 $name = $this->copy_until($this->token_equal); if ($name
=== '' && $this->char !== null && $space[0] === '') { break;
}
 if ($guard === $this->pos) {$this->char = (++$this->pos < $this->size) ?
$this>doc[$this->pos] : null; // next
continue;
 } 
  $guard = $this->pos;
 if ($this->pos >= $this->size - 1 && $this->char !== '>') {
 $node->nodetype = HDOM_TYPE_TEXT;
 $node->_[HDOM_INFO_END] = 0;
 $node->_[HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
 $node->tag = 'text';
 $this->link_nodes($node, false);
return true;
 }
 if ($this->doc[$this->pos - 1] == '<') {
 $node->nodetype = HDOM_TYPE_TEXT;
 $node->tag = 'text';
 $node->attr = array();
 $node->_[HDOM_INFO_END] = 0;
 $node->_[HDOM_INFO_TEXT] = substr(
 $this->doc,
 $begin_tag_pos,
 $this->pos - $begin_tag_pos - 1
 );
 $this->pos -= 2;
 $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
 $this->link_nodes($node, false);
return true;
 }
 if ($name !== '/' && $name !== '') { // this is a attribute name
 $space[1] = $this->copy_skip($this->token_blank);
 $name = $this->restore_noise($name); // might be a noisy name
if ($this->lowercase) { $name = strtolower($name); }
 $node->_[HDOM_INFO_SPACE][] = $space;
 $space = array( 
   $this->copy_skip($this->token_blank),
 '',);
 }
 } while ($this->char !== '>' && $this->char !== '/');
 $this->link_nodes($node, true);
 $node->_[HDOM_INFO_ENDSPACE] = $space[0];
 if ($this->copy_until_char('>') === '/') {
 $node->_[HDOM_INFO_ENDSPACE] .= '/';
 $node->_[HDOM_INFO_END] = 0;
 } else {
 if (!isset($this->self_closing_tags[strtolower($node->tag)])) {
 $this->parent = $node;
 }
 }
 $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
if ($node->tag === 'br') {
 $node->_[HDOM_INFO_INNER] = $this->default_br_text;
 }
return true;
 }
 protected function link_nodes(&$node, $is_child){
 $node->parent = $this->parent;
 $this->parent->nodes[] = $node;
if ($is_child) {
 $this->parent->children[] = $node;
}}
protected function as_text_node($tag){
 $node = new simple_html_dom_node($this);
 ++$this->cursor;
 $node->_[HDOM_INFO_TEXT] = '</' . $tag . '>'; 
$this->link_nodes($node, false);
 $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
return true;
 }
 protected function skip($chars){
 $this->pos += strspn($this->doc, $chars, $this->pos);
 $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
 }
 protected function copy_skip($chars){
 $pos = $this->pos;
 $len = strspn($this->doc, $chars, $pos);
 $this->pos += $len;
 $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
if ($len === 0) { return ''; } return substr($this->doc, $pos, $len);
 }
 protected function copy_until($chars){
 $pos = $this->pos;
 $len = strcspn($this->doc, $chars, $pos);
 $this->pos += $len;
 $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next return
substr($this->doc, $pos, $len);
}
function search_noise($text)
{ global $debug_object;
 if (is_object($debug_object)) { $debug_object->debug_log_entry(1); }
foreach($this->noise as $noiseElement) { if (strpos($noiseElement,
$text) !== false) { return $noiseElement;
 }}}
 function __toString(){return $this->root->innertext();} function
childNodes($idx = -1){return $this->root->childNodes($idx);} function
firstChild(){return $this->root->first_child();} function lastChild()
{return $this->root->last_child();}
} 
