<?php 
/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

/**
* QChecker Default Theme
* @author Achecker
* @author Joel Carvalho
* @version 1.0 2015.03.16
*/

?>

<div class="seq">
	<?php if (isset($this->prev_page)): ?>
		<?php echo _AC('previous_chapter'); ?>: <a href="frame_content.php?p=<?php echo $this->prev_page; ?>" accesskey="," title="<?php echo _AC($this->pages[$this->prev_page]['title_var']); ?> Alt+,"><?php echo _AC($this->pages[$this->prev_page]['title_var']); ?></a><br />
	<?php endif; ?>

	<?php if (isset($this->next_page)): ?>
		<?php echo _AC('next_chapter'); ?>: <a href="frame_content.php?p=<?php echo $this->next_page; ?>" accesskey="," title="<?php echo _AC($this->pages[$this->next_page]['title_var']); ?> Alt+,"><?php echo _AC($this->pages[$this->next_page]['title_var']); ?></a><br />
	<?php endif; ?>
</div>

<div class="tag">
	All text is available under the terms of the GNU Free Documentation License. 
</div>
</body>
</html>