<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/page', 'header'); ?>
  <?php
    // Featured image
    $thumb_id = get_post_thumbnail_id();
    // Image alt
    $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);

    $display_title = get_post_meta($post->ID, 'display_title', true);
    $kicker = get_post_meta($post->ID, 'kicker', true);
    $subtitle = get_post_meta($post->ID, 'subtitle', true);
    $intro = get_post_meta($post->ID, 'intro', true);
    $video_url = get_post_meta($post->ID, 'video_url', true);
    $hide_featured_image = get_post_meta($post->ID,'hide_featured_image', true);
    $caption = get_the_post_thumbnail_caption();
  ?>
  <div class="layout-container full--until-large">
    <div class="flex-container cf">
      <div class="shift-left--fluid column__primary bg--white can-be--dark-light no-pad--btm">
        <div class="pad--primary spacing">
          <?php
			$categories = array("suomenkieliset" => "fi", "ruotsinkieliset" => "sv", "englanninkieliset" => "en", "venajankieliset" => "ru");
			$return = array("fi" => "Palaa seurakuntalistaan", "en" => "Return to list", "sv" => "Tillbaka till listan", "ru" => "Return to list");

			foreach($categories as $cat => $lng) {
			  if(strpos($_SERVER['REQUEST_URI'], $cat) !== false) {
			    $lang = $lng;
			  }
			}

			$must_contain = array("adventist.fi", "/seurakunnat", "/page");
			$referer = @$_SERVER['HTTP_REFERER'];
			$valid = true;

			if(isset($referer))
			{
			  foreach($must_contain as $part) 
			  {
			    if(strpos($referer, $part) === false)
			    {
			        $valid = false;
			        break;
			    }
			  }
			}
			else $valid = false;                

			if(!$valid) {
			  $referer = "//".$_SERVER['SERVER_NAME'] . "/seurakunnat";
			}
                
            ?>
            <a href="<?php echo $referer; ?>">&laquo; <?php echo $return[$lang]; ?></a>
          <div class="text article__body spacing">
            <header class="article__header article__flow spacing--quarter">
              <h1 class="font--secondary--xl theme--secondary-text-color">
                <?php $pageTitle; ?>
                <?php if ($display_title): ?>
                  <?php echo $display_title; ?>
                <?php else: ?>
                  <?php the_title(); ?>
                <?php endif; ?>
              </h1>
              <?php if ($subtitle): ?>
                <h2 class="font--secondary--m"><?php echo $subtitle; ?></h2>
              <?php endif; ?>
              <?php if (in_category('news')): ?>
                <?php include(locate_template('patterns/components/share-tools.php')); ?>
              <?php endif; ?>
              <div class="article__meta">
                
                <?php
                  
                  $theme_options = get_option('alps_theme_settings');
                  $hide_author_global = $theme_options['hide_author_global'];
                  $hide_author_post = get_post_meta($post->ID, 'hide_author_post', true);
                ?>
              </div>
            </header>
            <?php if ($video_url): ?>
              <?php include(locate_template('patterns/components/featured-video.php')); ?>
            <?php else: ?>
              <?php if ($thumb_id && $hide_featured_image != 'true'): ?>
                <figure class="figure">
                  <div class="article__hero img-wrap">
                    <img src="<?php echo wp_get_attachment_image_src($thumb_id, "featured__hero--m")[0]; ?>" alt="<?php echo $alt; ?>" class="article__hero-img">
                  </div>
                  <?php if ($caption): ?>
                    <figcaption class="figcaption">
                      <p class="font--secondary--xs"><?php echo $caption; ?></p>
                    </figcaption>
                  <?php endif; ?>
                </figure>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($intro): ?>
              <h3><?php echo $intro; ?></h3>
            <?php endif; ?>            
            <?php the_content(); ?>
            <?php 
                $strings = array(
                  "fi" => array(
                      "founded" => "Perustettu",
                      "phone" => "Puh.",
                      "own_website" => "Kotisivu",
                      "reference" => "Pankkiviite",
                      "email" => "Sähköposti"
                    ),
                  "en" => array(
                      "founded" => "Founded",
                      "phone" => "Tel.",
                      "own_website" => "Website",
                      "reference" => "Bank reference",
                      "email" => "Email"
                    ), 
                  "sv" => array(
                      "founded" => "Grundad",
                      "phone" => "Telefon",
                      "own_website" => "Hemsida",
                      "reference" => "Bankreferens",
                      "email" => "E-post"
                    ), 
                  "ru" => array(
                      "founded" => "Founded",
                      "phone" => "Tel.",
                      "own_website" => "Website",
                      "reference" => "Bank reference",
                      "email" => "Email"
                    )
                );
                
                $post_fields = get_fields($p->ID);
                  
                $street_address = $post_fields['address'];
                $phone = $post_fields['phone'];
                $website = $post_fields['website'];
                $email = str_replace("@", "(a)", $post_fields['email']);
                
                $city = $post_fields['location'];                
                $founded = $post_fields['founded'];
                $bank = $post_fields['reference'];
                
                $meta = get_post_meta($post->ID);
              ?>               
              <ul style="list-style-type:none;">
                <?php if($street_address != ""): ?>
                  <li><?php echo $street_address; ?></li>
                <?php endif; ?>
                
                <?php if($phone != ""): ?>
                  <li><?php echo $strings[$lang]["phone"] ." <a href='tel:". $phone ."'>". $phone ."</a>"; ?></li>
                <?php endif; ?>
                
                <?php if($email != ""): ?>
                  <li><?php echo $strings[$lang]["email"] ." ". $email; ?></li>
                <?php endif; ?>
                
                <?php if ($bank != ""): ?>
                  <li><?php echo $strings[$lang]["reference"] . " ". $bank; ?></li>
                <?php endif; ?>
                
                <?php if ($website != ""): ?>
                  <li><?php echo $strings[$lang]["own_website"] . " <a target='_blank' href='". $website ."'>".substr($website, strpos($website, "://")+3)."</a>"; ?></li>
                <?php endif; ?>
                
                <?php if($founded != ""): ?> 
                  <li><?php echo $strings[$lang]["founded"] . " " . $founded; ?></li>
                <?php endif; ?>
              </ul>
              
              <?php
              
                if(have_rows("officers")) {
                  while(have_rows("officers")) : the_row();
                  ?>
                  <ul style="list-style-type: none;">
                    <li style="font-weight: bold;"><?php the_sub_field("office"); ?></li>
                    <li><?php the_sub_field("name"); ?></li>
                    <li><?php echo "<a href='tel:"; the_sub_field("phone"); echo "'>"; the_sub_field("phone"); echo "</a>"; ?></li>
                    <li><?php the_sub_field("email"); ?></li>
                  </ul>
                  <?php
                  endwhile;
                  $key = "AIzaSyDc6nWThME67OvYhGOfskW71pQG1OiVreU";
                  $location = unserialize($meta['mapLocation'][0]);
                  
                  ?>
                  <iframe width="450" height="350" src="https://google.com/maps/embed/v1/place?q=<?php echo $location['lat'] . ",". $location['lng']; ?>&amp;zoom=12&amp;language=<?php echo $lang; ?>&amp;key=<?php echo $key; ?>"></iframe>
                  <?php
                }
            ?>
          </div>
        </div>
        <?php include(locate_template('templates/block-layout.php')); ?>
      </div> <!-- /.shift-left--fluid -->
      <?php get_sidebar(); ?>
    </div> <!-- /.flex-container -->
  </div> <!-- /.layout-container -->
<?php endwhile; ?>
