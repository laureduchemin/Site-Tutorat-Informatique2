<?php
/**
 * @version SVN: $Id$
 * @package    com_userprofile
 * @author     Mathias Hortig {@link http://tuts4you.de/}
 * @license    GNU/GPL
 */

defined('_JEXEC') or die('Restricted access'); ?>
<p>You can support us with a voluntary donation.</p><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input name="cmd" value="_s-xclick" type="hidden" /> <input name="hosted_button_id" value="DGQEACXQG2U62" type="hidden" /> <input border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." src="https://www.paypalobjects.com/de_DE/AT/i/btn/btn_donateCC_LG.gif" type="image" /> <img border="0" alt="" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" /></form>
<form action="<?php echo $this->action; ?>" method="post" name="adminForm" id="adminForm">
<div style="width:100%;float:left;">
  <textarea style="width:100%; height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $this->content; ?></textarea>
</div>
<input type="hidden" name="option" value="<?php echo JRequest::getCmd( 'option' );  ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
