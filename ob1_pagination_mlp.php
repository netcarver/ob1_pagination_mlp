<?php
$plugin['name'] = 'ob1_pagination_mlp';
$plugin['version'] = '2.5mlp';
$plugin['author'] = 'Henrik Jönsson';
$plugin['author_uri'] = 'http://rise.lewander.com/';
$plugin['description'] = 'Creates a Google inspired pagination.';
$plugin['type'] = '0';	# public-only

@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/**
Plugin: ob1_pagination
URL: http://rise.lewander.com/textpattern/ob1-pagination
Released under the Creative Commons Attribution-Share Alike 3.0 Unported, http://creativecommons.org/licenses/by-sa/3.0/
*/

function ob1_pagination($atts) 
	{
	global $thispage,$q,$prefs,$pretext;
	extract($pretext);
	if(is_array($thispage)) extract($thispage);
	extract($prefs);
	if(!isset($pg)) $pg=1;
	$numberOfTabs = (empty($thispage)) ? 1: $numPages;

	if( defined('L10N_DEBUG_URLREWRITE') && L10N_DEBUG_URLREWRITE) dmp($pretext);
	
	extract(lAtts(array(
		'maximumtabs'=>'11', # maximum number of tabs displayed
		'firsttext'=>'&#171;',
		'firsttexttitle'=>'First',
		'previoustext'=>'&#8249;',
		'previoustexttitle'=>'Previous',
		'lasttext'=>'&#187;',
		'lasttexttitle'=>'Last',
		'nexttext'=>'&#8250;',
		'nexttexttitle'=>'Next',
		'pagetext'=>'Page',
		'ulid'=>'',
		'ulclass'=>'',
		'liclass'=>'', # default class, added to every <li>
		'liselected'=>'', # selected class, added to the current tab
		'liselectedtext'=>'', # if you want the selected <li> to contain something else then the number, add it here
		'liempty'=>'', # empty class, added to the <li> that do not hold an anchor <a>
		'linkcurrent'=>'0',
		'outputlastfirst'=>'1',
		'outputnextprevious'=>'1',
		'reversenumberorder'=>'0', # want the numbers reversed? no sweat. change to 1
		'moretabsdisplay'=>'', # may contain before or after or both if they're comma-separated
		'moretabstext'=>'...',
		'wraptag'=>'',
		'mode'=>'relative',	# make this 'full' to output non-relative URLs. The MLP pack version will automatically do this.
	),$atts));

	$url = '';
	if( defined('L10N_SNIPPET_IO_HEADER') || $mode === 'full')
		{
		$url = hu.ltrim($req,'/');
		$cn_qs  = strlen($qs);
		if( $cn_qs > 0 )
			$cn_qs += 1;
		$cn_req = strlen($url);
		$url = substr($url,0,$cn_req-$cn_qs);
		}
	if( defined('L10N_DEBUG_URLREWRITE') && L10N_DEBUG_URLREWRITE) dmp($url);
	
	$ulid=(empty($ulid)) ? '' : ' id="'.$ulid.'"';
	$ulclass=(empty($ulclass))? '' : ' class="'.$ulclass.'"';

	$addToURL = ($permlink_mode=='messy') ? '&amp;s='.$s : '' ;
	$addToURL .= ($c) ? '&amp;c='.$c : '';

	# if we got tabs, start the outputting
	if($numberOfTabs>1)
		{
		if($maximumtabs==1) $maximumtabs=11; # using just one tab is folly! folly i say

		# this is for the search
		if($q and (is_callable(@ob1_advanced_search)))
			{
			# if you're using ob1_advanced_search [v1.0b and above], add some stuff to the URL
			$ob1ASGet = array('rpp','wh','ww','oc','ad','sd','ed','bts');
			foreach($ob1ASGet as $val) 
				{
				$$val = (!empty($_GET[$val])) ? '&amp;'.$val.'='.urlencode($_GET[$val]) : '';
				}
			$addToURL .= '&amp;q='.urlencode($q).$rpp.$wh.$ww.$oc.$ad.$sd.$ed.$bts;
			unset($ob1ASGet,$rpp,$wh,$ww,$oc,$ad,$sd,$ed,$bts);
			}
		elseif($q)
			{
			$addToURL .= '&amp;q='.urlencode($q);
			}

		if($numberOfTabs>$maximumtabs)
			{
			$loopStart = $pg-floor($maximumtabs/2);
			$loopEnd = $loopStart+$maximumtabs;
			if($loopStart<1)
				{
				$loopStart = 1;
				$loopEnd = $maximumtabs+1;
				}
			if($loopEnd>$numberOfTabs)
				{
				$loopEnd = $numberOfTabs+1;
				$loopStart = $loopEnd - $maximumtabs;
				if($loopStart<1) $loopStart = 1;
				}
			}
		else
			{
			$loopStart = 1;
			$loopEnd = $maximumtabs+1;
			}
		if($loopEnd>$numberOfTabs)
			{
			$loopEnd = $numberOfTabs+1;
			}
		$out=array();
		if($pg>1)
			{
			$out[] = ($outputlastfirst) ?    '<li class="'.$liclass.'"><a href="'.$url.'?pg=1'.       $addToURL.'" title="'.$firsttexttitle.   '">'.$firsttext.   '</a></li>'.n : '';
			$out[] = ($outputnextprevious) ? '<li class="'.$liclass.'"><a href="'.$url.'?pg='.($pg-1).$addToURL.'" title="'.$previoustexttitle.'">'.$previoustext.'</a></li>'.n : '';
			}
		else
			{
			$out[] = ($outputlastfirst) ?    '<li class="'.$liempty.' '.$liclass.'">'.$firsttext.   '</li>'.n : '';
			$out[] = ($outputnextprevious) ? '<li class="'.$liempty.' '.$liclass.'">'.$previoustext.'</li>'.n : '';
			}

		if(in_list('before',$moretabsdisplay) and $loopStart>1)
			{
			$out[] = '<li class="'.$liclass.' '.$liempty.'">'.$moretabstext.'</li>';
			}

		for($i=$loopStart;$i<$loopEnd;$i++)
			{
			if($i==$pg)
				{
				$out[] = '<li class="'.$liselected.' '.$liclass;
				$out[] = ($linkcurrent) ? '">' : ' '.$liempty.'">';
				$out[] = ($linkcurrent) ? '<a href="'.$url.'?pg='.$i.$addToURL.'" title="'.$pagetext : '';
				if($reversenumberorder)
					{
					$out[] = ($linkcurrent) ? ' '.($numberOfTabs-$i+1).'">' : '';
					$out[] = ($liselectedtext) ? $liselectedtext : ($numberOfTabs-$i+1);
					}
				else
					{
					$out[] = ($linkcurrent) ? ' '.$i.'">' : '';
					$out[] = ($liselectedtext) ? $liselectedtext : $i;
					}
				$out[] = ($linkcurrent) ? '</a>' : '';
				$out[] = '</li>'.n;
				}
			else
				{
				$out[] = '<li class="'.$liclass.'"><a href="'.$url.'?pg='.$i.$addToURL.'" title="'.$pagetext;
				$out[] = ($reversenumberorder) ? ' '.($numberOfTabs-$i+1).'">'.($numberOfTabs-$i+1) : ' '.$i.'">'.$i;
				$out[] = '</a></li>'.n;
				}
			}

		if(in_list('after',$moretabsdisplay) and $loopEnd<=$numberOfTabs)
			{
			$out[] = '<li class="'.$liclass.' '.$liempty.'">'.$moretabstext.'</li>';
			}

		if($pg==$numberOfTabs)
			{
			$out[] = ($outputnextprevious) ? '<li class="'.$liempty.' '.$liclass.'">'.$nexttext.'</li>'.n : '';
			$out[] = ($outputlastfirst) ?    '<li class="'.$liempty.' '.$liclass.'">'.$lasttext.'</li>'.n : '';
			}
		else
			{
			$out[] = ($outputnextprevious) ? '<li class="'.$liclass.'"><a href="'.$url.'?pg='.($pg+1).$addToURL.      '" title="'.$nexttexttitle.'">'.$nexttext.'</a></li>'.n : '';
			$out[] = ($outputlastfirst)    ? '<li class="'.$liclass.'"><a href="'.$url.'?pg='.$numberOfTabs.$addToURL.'" title="'.$lasttexttitle.'">'.$lasttext.'</a></li>'.n : '';
			}
		return ($wraptag) ? tag('<ul'.$ulclass.$ulid.'>'.n.join('', $out).'</ul>',$wraptag) : '<ul'.$ulclass.$ulid.'>'.n.join('', $out).'</ul>';
		}
	else
		{
		return false;
		}
	} # let's end it here and now
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<style type="text/css">
@media all {
	#helpContents * {
		border: 0;
		margin: 0;
	}
	#helpContents {
		font-family: 'Lucida Grande',Helvetica,Arial,sans-serif;
		font-size: 11px;
		line-height:1.4em;
		padding:10px;
	}
	#helpContents ul {
		list-style: square;
	}
	#helpContents h1,h2,h3 {
		font-weight: bold;
		font-family: 'Lucida Grande',Helvetica,Arial,sans-serif;
	}
	#helpContents h1 {
		margin-bottom: 4px;
	}
	#helpContents h2 {
		margin-top: 10px;
		text-transform: uppercase;
	}
	#helpContents h3 {
		font-size: 1em;
		margin-top: 10px;
		margin-left: 10px;
	}
	#helpContents li {
		margin-top: 4px;
	}
	#helpContents p {
		font-family: 'Trebuchet MS',Helvetica,Arial,sans-serif;
		font-size: 1.1em;
		margin-top: 10px;
		margin-left: 20px;
	}
	#helpContents strong, #helpContents b {
		font-family: 'Trebuchet MS',Helvetica,Arial,sans-serif;
		font-size: 1em;
	}
	#helpContents ul, #helpContents ol {
		margin-left: 30px;
	}
	#helpContents blockquote {
		padding: 8px 4px 8px 4px;
	}
}
@media screen {
	#helpContents {
		background-color: #005e7e;
		border: 1px dotted #005e7e;
		width: 700px;
		color: #c3e9f6;
	}
	#helpContents a, #helpContents a.visited {
		color: #42b3d7;
		text-decoration: none;
		border-bottom: 1px dotted #42b3d7;
	}
	#helpContents a[title='External link'] {
		color: #deebcd;
		border-bottom: 1px dotted #deebcd;
	}
	#helpContents a:hover {
		color: #6bd5fa;
		border-bottom: 0;
	}
	#helpContents acronym {
		border-bottom: 1px dotted #c3e9f6;
		cursor: help;
	}
	#helpContents code, #helpContents blockquote {
		background-color: #42b3d7;
		padding-left: 4px;
		padding-right: 4px;
	}
	#helpContents blockquote {
		margin-top: 10px;
		padding: 4px;
		border: 1px dotted #005e7e;
		color: #005e7e;
	}
}
@media print {
	#helpContents {
		border: 1px dotted #ccc;
		color: #000;
	}
	#helpContents a, #helpContents a:visited {
		color: #333;
	}
	#helpContents div.index ul {
		display: none;
	}
}
</style>
<div id="helpContents">


	<h1>ob1-pagination v2.5mlp [2007-10-13]</h1>

<div class="index">

	<ul>
		<li><a href="#pluginSummary">Summary</a></li>
		<li><a href="#pluginAttributes">Attributes</a>
	<ul>
		<li><a href="#pluginMaximumTabs">maximumtabs</a></li>
		<li><a href="#pluginLinkCurrent">linkcurrent</a></li>
		<li><a href="#pluginOutputLastFirst">outputlastfirst</a></li>
		<li><a href="#pluginOutputNextPrevious">outputnextprevious</a></li>
		<li><a href="#pluginUlID">ulid</a></li>
		<li><a href="#pluginUlClass">ulclass</a></li>
		<li><a href="#pluginLiClass">liclass</a></li>
		<li><a href="#pluginLiSelected">liselected</a></li>
		<li><a href="#pluginLiSelectedText">liselectedtext</a></li>
		<li><a href="#pluginLiEmpty">liempty</a></li>
		<li><a href="#pluginFirstText">firsttext</a></li>
		<li><a href="#pluginFirstTextTitle">firsttexttitle</a></li>
		<li><a href="#pluginPreviousText">previoustext</a></li>
		<li><a href="#pluginPreviousTextTitle">previoustexttitle</a></li>
		<li><a href="#pluginNextText">nexttext</a></li>
		<li><a href="#pluginNextTextTitle">nexttexttitle</a></li>
		<li><a href="#pluginLastText">lasttext</a></li>
		<li><a href="#pluginLastTextTitle">lasttexttitle</a></li>
		<li><a href="#pluginPageText">pagetext</a></li>
		<li><a href="#pluginReverseNumberOrder">reversenumberorder</a></li>
		<li><a href="#pluginMoreTabsDisplay">moretabsdisplay</a></li>
		<li><a href="#pluginMoreTabsText">moretabstext</a></li>
		<li><a href="#pluginWrapTag">wraptag</a></li>
		<li><a href="#pluginMode">mode</a></li>
	</ul></li>
		<li><a href="#pluginExamples">Examples</a></li>
		<li><a href="#pluginChangeLog">Changelog</a></li>
		<li><a href="http://rise.lewander.com/" title="External link">Author <span class="caps">URL</span></a></li>
		<li><a href="http://forum.textpattern.com/viewtopic.php?id=3724" title="External link"><span class="caps">TXP</span> forum thread</a></li>
		<li><a href="http://rise.lewander.com/textpattern/ob1-pagination" title="External link">Plugin <span class="caps">URL</span></a></li>
		<li><a href="http://creativecommons.org/licenses/by-sa/3.0/" title="External link">License <span class="caps">URL</span></a></li>
	</ul>

</div>

	<h2 id="pluginSummary">Summary</h2>

	<p>This plugin creates a navigation bar as seen on <a href="http://www.google.com" title="External link">Google</a> when you limit articles or search for something. It has a wide variety of attributes so you are able to customize it until you drop.</p>

	<h2 id="pluginAttributes">Attributes</h2>

	<h3 id="pluginMaximumTabs">maximumtabs</h3>

	<p>The maximum number of tabs to display. Can <em>not</em> be set to 1 since that kills the whole idea of this plugin.</p>

	<ul>
		<li><em>11</em> &#8211; default.</li>
	</ul>

	<h3 id="pluginLinkCurrent">linkcurrent</h3>

	<p>Wheter or not the current page number is a link or not.</p>

	<ul>
		<li><em>0</em> &#8211; No link (default).</li>
		<li><em>1</em> &#8211; Link.</li>
	</ul>

	<h3 id="pluginOutputLastFirst">outputlastfirst</h3>

	<p>Wheter or not to output the First/Last tabs.</p>

	<ul>
		<li><em>1</em> &#8211; Output (default).</li>
		<li><em>0</em> &#8211; Don&#8217;t.</li>
	</ul>

	<h3 id="pluginOutputNextPrevious">outputnextprevious</h3>

	<p>Wheter or not to output the Next/Previous tabs.</p>

	<ul>
		<li><em>1</em> &#8211; Output (default).</li>
		<li><em>0</em> &#8211; Don&#8217;t.</li>
	</ul>

	<h3 id="pluginUlID">ulid</h3>

	<p>Enables you to set a id to the <code>&#60;ul&#62;</code> that wraps the output.</p>

	<h3 id="pluginUlClass">ulclass</h3>

	<p>Enables you to set a class to the <code>&#60;ul&#62;</code> that wraps the output.</p>

	<h3 id="pluginLiClass">liclass</h3>

	<p>Sets a default class to all <code>&#60;li&#62;</code> tabs.</p>

	<h3 id="pluginLiSelected">liselected</h3>

	<p>Adds this class to the currently active <code>&#60;li&#62;</code> tab.</p>

	<h3 id="pluginLiSelectedText">liselectedtext</h3>

	<p>If you want to have the selected tab output something else then the number, add it here.</p>

	<h3 id="pluginLiEmpty">liempty</h3>

	<p>Adds this class to all <code>&#60;li&#62;</code> tabs that are empty.</p>

	<h3 id="pluginFirstText">firsttext</h3>

	<p>Enables you to alter the text inside the first link.</p>

	<h3 id="pluginFirstTextTitle">firsttexttitle</h3>

	<p>Enables you to alter the title of the first link.</p>

	<h3 id="pluginPreviousText">previoustext</h3>

	<p>Enables you to alter the text inside the previous link.</p>

	<h3 id="pluginPreviousTextTitle">previoustexttitle</h3>

	<p>Enables you to alter the title of the previous link.</p>

	<h3 id="pluginNextText">nexttext</h3>

	<p>Enables you to alter the text inside the next link.</p>

	<h3 id="pluginNextTextTitle">nexttexttitle</h3>

	<p>Enables you to alter the title of the next link.</p>

	<h3 id="pluginLastText">lasttext</h3>

	<p>Enables you to alter the text inside the last link.</p>

	<h3 id="pluginLastTextTitle">lasttexttitle</h3>

	<p>Enables you to alter the title of the last link.</p>

	<h3 id="pluginPageText">pagetext</h3>

	<p>Enables you to alter the text in the titles of the page tabs.</p>

	<h3 id="pluginReverseNumberOrder">reversenumberorder</h3>

	<p>Makes it possible to reverse the numbers in the tabs.</p>

	<ul>
		<li><em>0</em> &#8211; 1,2 and so on (default).</li>
		<li><em>1</em> &#8211; 2,1 and so on.</li>
	</ul>

	<h3 id="pluginMoreTabsDisplay">moretabsdisplay</h3>

	<p>Want to get a <code>&#60;li&#62;</code> that states that there are more tabs before or after the ones currently viewable? Make this value to a 1 then.</p>

	<h3 id="pluginMoreTabsText">moretabstext</h3>

	<p>If you just added the attribute moretabsdisplay to 1, then you need to define where it should show. Want it in the start if there are pages below the current ones? Or do you want it after if there are more tabs available? You don&#8217;t need to choose, just comma-separate the below values and you&#8217;re done.</p>

	<ul>
		<li>before</li>
		<li>after</li>
	</ul>

	<h3 id="pluginWrapTag">wraptag</h3>

	<p>No brainer. Does what it usually does in <acronym title="Textpattern"><span class="caps">TXP</span></acronym>. Wraps the tag specified around the output.</p>

	<h3 id="pluginMode">mode</h3>

	<p>Controls the kind of URLs output by the plugin. Defaults to 'relative' but any other value will cause the plugin to try to output full URLs. NB: When the MLP Pack is installed the plugin will try to output full URLs no matter what this attribute is set to.</p>

	<h2 id="pluginExamples">Examples</h2>

<blockquote><code>&#60;txp:ob1_googlenav firsttext=&#34;First&#34; previoustext=&#34;Previous&#34; nexttext=&#34;Next&#34; lasttext=&#34;Last&#34; /&#62;</code></blockquote>

	<p>This outputs if there are three pages</p>

	<ul>
		<li>First</li>
		<li>Previous</li>
		<li>1</li>
		<li><a href="?pg=2">2</a></li>
		<li><a href="?pg=3">3</a></li>
		<li><a href="?pg=2">Next</a></li>
		<li><a href="?pg=3">Last</a></li>
	</ul>

	<p><strong>Do note</strong> that this plugin creates an unordered list that is meant to be <em>styled by you</em>, through <acronym title="Cascading StyleSheets"><span class="caps">CSS</span></acronym>.</p>

	<h2 id="pluginChangelog">Changelog</h2>

	<h3>Version 2.5 [2007-10-13]</h3>

	<p><strong>Changed features</strong></p>

	<ul>
		<li>New name on popular demand: ob1_googlenav became ob1_pagination.</li>
		<li>Removed <code>outputlinktags</code> feature. In <span class="caps">TXP</span> 4.x it&#8217;s hard to get it to work. Maybe will release it a separate plugin, who knows?</li>
	</ul>

	<p><strong>New features</strong></p>

	<ul>
		<li>Attribute <a href="#pluginOutputNextPrevious">outputnextprevious</a> added. Does what the name specifies.</li>
	</ul>

	<h3>Version 2.4 [2007-09-18]</h3>

	<p><strong>Bug fixes</strong></p>

	<ul>
		<li>Now works with <acronym title="Textpattern"><span class="caps">TXP</span></acronym> 4, thanks to <a href="http://gerhardlazu.com/blog/44/ob1googlenav-20-corrections" title="External link">Gerhard</a> for adressing the most parts of this. This included:
	<ul>
		<li>messy_urls didn&#8217;t work after <acronym title="Textpattern"><span class="caps">TXP</span></acronym> RC3 (or something).</li>
		<li>breaking <span class="caps">XHTML</span> since the use of &#38; in the <acronym title="Uniform Resource Locator"><span class="caps">URL</span></acronym>s.</li>
	</ul></li>
		<li>Attribute outputfirstlast didn&#8217;t work properly. Now does what it&#8217;s supposed to do.</li>
		<li>Attribute liselected was not added if not the attribute linkcurrent was chosen. Fixed.</li>
	</ul>

	<p><strong>Changed features</strong></p>

	<ul>
		<li>Attribute maxtab changed to <a href="#pluginMaximumTabs">maximumtabs</a> for better naming procedure. Still does the same.</li>
		<li><a href="#pluginLiClass">liclass</a> is added to all <code>&#60;li&#62;</code>.</li>
		<li><a href="#pluginLiEmpty">liempty</a> is added to all empty <code>&#60;li&#62;</code>&#8217;s, not just setting it as one and only class.</li>
		<li><a href="#pluginLiSelected">liselected</a> is added to the selected <code>&#60;li&#62;</code> like <code>liempty</code> above.</li>
	</ul>

	<p><strong>New features</strong></p>

	<ul>
		<li>New attributes:
	<ul>
		<li><a href="#pluginLiSelectedText">liselectedtext</a></li>
		<li><a href="#pluginReverseOrderNumber">reversenumberorder</a></li>
		<li><a href="#pluginMoreTabsDisplay">moretabsdisplay</a></li>
		<li><a href="#pluginMoreTabsText">moretabstext</a></li>
		<li><a href="#pluginWrapTag">wraptag</a></li>
	</ul></li>
	</ul>

	<p>Read up on them above.</p>

	<h3>Older versions</h3>

	<ul>
		<li>v2.0 &#8211; Added support for it to be used with my <a href="http://rise.lewander.com/textpattern/ob1-advanced-search">ob1_advanced_search</a> plugin.</li>
		<li>v1.1 &#8211; Fixed the bug that it did not add any category. Also did not work in messy mode.</li>
		<li>v1.0 &#8211; Initial release.</li>
	</ul>

</div>
# --- END PLUGIN HELP ---
-->
<?php
}
?>