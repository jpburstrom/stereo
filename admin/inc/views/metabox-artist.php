<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Metabox for playlist, showing playlist artist
 */

if ( !defined( 'ABSPATH' ) )
    die( '-1' );

$class = "";
if (!$other) $class = "hide-if-js";
var_export($selected);
?>

<?php if (stereo_option('create_artist_cpt')): ?>

<?php foreach ($artists as $artist) { echo $artist->ID . "|"; } ?>
<label for="stereo_artist" class="screen-reader-text"><?php echo stereo_option("artist_singular") ?></label>
<select id="stereo_artist" name="stereo_artist">
    <?php foreach ($artists as $artist): ?>
    <?php $sel = ($selected == $artist->ID) ? "selected='selected'" : ""; ?>
    <option <?php echo $sel ?> value="<?php echo $artist->ID?>"><?php echo $artist->post_title?></option>
<?php endforeach; ?>
    <?php $sel = ($selected == -1) ? "selected='selected'" : ""; ?>
    <optgroup label="---"></optgroup>
    <option <?php echo $sel ?> value="-1"><?php printf(__("Other %s", "stereo"), stereo_option("artist_singular")) ?></option>
</select>
<label for="stereo_other_artist" class="screen-reader-text"><?php printf(__("Name of other %s"), stereo_option("artist_singular"))?></label>
<input value="<?php echo $other ?>" class="<?php echo $class?>" placeholder="<?php printf(__("Name of other %s", "stereo"), stereo_option("artist_singular"))?>" id="stereo_other_artist" name="stereo_other_artist" type="text"/>
<input value="<?php echo $selected?>" name="stereo_current_artist" type="hidden"/>
<small><a href="post-new.php?post_type=stereo_artist" title="Create new <?php echo stereo_option("artist_singular")?>">New <?php echo stereo_option("artist_singular")?></a></small>

<?php else: ?>

<label for="stereo_other_artist" class="screen-reader-text"><?php printf(__("Name of %s"), stereo_option("artist_singular"))?></label>
<input value="<?php echo $other ?>" placeholder="<?php printf(__("Name of %s", "stereo"), stereo_option("artist_singular"))?>" id="stereo_other_artist" name="stereo_other_artist" type="text"/>
<input value="<?php echo $selected?>" name="stereo_current_artist" type="hidden"/>

<?php endif; ?>
