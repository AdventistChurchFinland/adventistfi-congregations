<?php
  // Get all zip codes and coordinates of all gongregations

  $q = get_posts(['post_type' => 'adventistfi_congreg', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
  
  $zipCodes = array();
  $coords = array();
  for($i=0;count($q)>$i;$i++) 
  {
    $meta = get_post_meta($q[$i]->ID);
    $location = unserialize($meta['mapLocation'][0]);
    
    $zipCodes["".get_post_field("zipcode", $q[$i])][] = $q[$i];
    
    $coords[] = [
      'lat' => doubleval($location['lat']),
      'lng' => doubleval($location['lng']),
      'data' => $q[$i]
    ];
  }
  
  function degToRad($d)
  {
    return $d * pi() / 180;
  }
  
  function DistanceKm($start, $dest)
  {
    $earthRadius = 6371;
    
    $latDeg = doubleval($dest['lat']) - doubleval($start['lat']);
    $dLat = degToRad($latDeg);
    
    $lngDeg = doubleval($dest['lng']) - doubleval($start['lng']);
    $dLng = degToRad($lngDeg);
    
    $start['lat'] = degToRad(doubleval($start['lat']));
    $dest['lat'] = degToRad(doubleval($dest['lat']));
    
    $a = sin($dLat/2) * sin($dLat/2) + sin($dLng/2) * sin($dLng/2) * cos($start['lat']) * cos($dest['lat']);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    $distance = $earthRadius * $c;
    
    if($distance < 1)
    {
      $distance = round($distance, 1);
    }
    else
    {
      $distance = round($distance);
    }
    
    return str_replace(".", ",", $distance);
  }
  
  function compare($a, $b)
  {
    if($a['latDiff'] > $b['latDiff'])
      return 1;
    elseif($a['latDiff'] == $b['latDiff'])
      return 0;
    else
      return -1;
  }

  function coordSearchRange($location)
  {
    $range = [];
    
    if(strpos($location, ",") !== false)
    {
      $c = explode(",", $location);
      $lat = doubleval($c[0]);
      $lng = doubleval($c[1]);
      
      $lat_small = $lat - 0.5;
      $lat_large = $lat + 0.5;
      
      $range['lat'][0] = $lat_small;
      $range['lat'][1] = $lat_large;
      
      $lng_small = $lng - 0.5;
      $lng_large = $lng + 0.5;
      
      $range['lng'][0] = $lng_small;
      $range['lng'][1] = $lng_large;
    }
    return $range;
  }

  function zipSearchRange($start)
  {
    $zipArea = substr(trim($start), 0, 2);
    $range = [];

    $n = 0;
    while($n < 990)
    {
      $n += 10;
      $range[] = $zipArea ."". ($n < 100 ? "0".$n : $n);
    }
    return $range;
  }

  $carousel_type = '';
  if (is_page() || is_single()) {
    $carousel_type = get_post_meta($post->ID, 'carousel_type', true);
  }
  
  $type = get_post_type($post);
?>
<script>
  function locate() 
  {
    if(navigator.geolocation) 
    {
      navigator.geolocation.getCurrentPosition(processLocation);
      
      $('#gettingLocationMessage').fadeIn(200, 'easeOutSine', function()
      {
        setTimeout(function()
        {
          $('#gettingLocationMessage').html("Haetaan yhä sijantia... Odota.");
        }, 10000);
      });
    }
    else
    {
      alert("Selaimesi ei valitettavasti tue tätä ominaisuutta.");
    }
  }
  
  function processLocation(e) 
  {
    var location = e.coords.latitude + "," + e.coords.longitude;
    var field = document.getElementById('geolocation');
    field.value = location;
    document.getElementById('locate').submit();
  }
  
  function checkLength()
  {
    var zip = document.getElementById('zipcode').value;
    var zipLength = zip.length;
    if(zipLength != 5)
    {
      alert("Postinumerossa on oltava 5 numeroa");
      return false;
    }
    
    if(isNan(parseInt(zip)))
    {
      alert("Anna numeerinen arvo.");
      return false;
    }
  }
</script>
<?php
  use Roots\Sage\Titles;
  global $post;
  $display_title = '';
  $kicker = '';
  $header_block_text = '';
  $header_block_title = '';
  $header_block_subtitle = '';
  $header_block_image = '';
  $header_background_image = '';
  if (is_page() || is_single()) {
    $display_title = get_post_meta($post->ID, 'display_title', true);
    $kicker = get_post_meta($post->ID, 'kicker', true);
    $header_block_text = get_post_meta($post->ID, 'header_block_text', true);
    $header_block_title = get_post_meta($post->ID, 'header_block_title', true);
    $header_block_subtitle = get_post_meta($post->ID, 'header_block_subtitle', true);
    $header_block_image = get_post_meta($post->ID,'header_block_image', true);
    $header_background_image = get_post_meta($post->ID, 'header_background_image', true);
  }

  if (is_single()) {
    // SHOW YOAST PRIMARY CATEGORY, OR FIRST CATEGORY
    $category = get_the_category();
    // If post has a category assigned.
    if ($category) {
      $kicker = '';
      $display_title = '';
      if (class_exists('WPSEO_Primary_Term')) {
        // Show the post's 'Primary' category, if this Yoast feature is available, & one is set
        $wpseo_primary_term = new WPSEO_Primary_Term('category', get_the_id());
        $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
        $term = get_term($wpseo_primary_term);
        if (is_wp_error($term)) {
          // Default to first category (not Yoast) if an error is returned
          $kicker = '';
          $display_title = $category[0]->name;
        } else {
          // Yoast Primary category
          if ($term->parent != 0) {
            $term_parent = get_term($term->parent, 'category')->name;
            $kicker = $term_parent;
            $display_title = $term->name;
          } else {
            $kicker = '';
            $display_title = $term->name;
          }
        }
      }
      else {
        // Default, display the first category in WP's list of assigned categories
        $kicker = '';
        $display_title = $category[0]->name;
      }
    }
  }
?>
<?php
  if (!empty($header_background_image)):
?>
  <style type="text/css">
    .header-swath--with-image {
      background-image: url(<?php echo wp_get_attachment_image_url( $header_background_image, 'featured__hero--m' ); ?>);
    }
    @media (min-width: 800px) {
      .header-swath--with-image {
        background-image: url(<?php echo wp_get_attachment_image_url( $header_background_image, 'featured__hero--l' ); ?>);
      }
    }
    @media (min-width: 1100px) {
      .header-swath--with-image {
        background-image: url(<?php echo wp_get_attachment_image_url( $header_background_image, 'featured__hero--xl' ); ?>);
      }
    }
  </style>
<?php endif; ?>

<header class="header__swath theme--primary-background-color blend-mode--multiply <?php if (!empty($header_background_image)): echo "header-swath--with-image"; endif; ?> <?php if ($header_block_text == 'true'): echo "header-swath--with-text"; endif; ?>">
  <div class="layout-container cf">
    <?php if ($header_block_text == 'true'): ?>
      <div class="header__text">
        <div class="unify show-at--small">
          <h2 class="font--secondary--l upper white"><?php echo $header_block_title; ?></h2>
          <?php if ($header_block_subtitle): ?>
            <h3 class="font--secondary--m white--trans"><?php echo $header_block_subtitle; ?></h3>
          <?php endif; ?>
        </div>
        <?php if ($header_block_image): ?>
          <div class="header__logo">
            <img src="<?php echo wp_get_attachment_image_url( $header_block_image, 'thumbnail' ); ?>" width="80" height="80" alt="<?php get_post_meta($header_block_image, '_wp_attachment_metadata', true); ?>">
          </div>
        <?php endif; ?>
      </div> <!-- /.header__text -->
    <?php endif; ?>
    <div class="flex-container cf">
      <div class="shift-left--fluid">
        <span class="kicker white">
          <?php if ($kicker && !is_category() && !is_home()): ?>
            <?php echo $kicker; ?>
          <?php elseif (is_page() && $post->post_parent != '0'): ?>
            <?php echo get_the_title($post->post_parent); ?>
          <?php endif; ?>
        </span>
        <h1 class="font--tertiary--xl white">Adventtiseurakunnat</h1>
      </div>
      <div class="shift-right--fluid"></div> <!-- /.shift-right--fluid -->
    </div>
  </div>
</header> <!-- /.header__swath -->
<div class="layout-container full--until-large">
  <div class="flex-container cf">
    <div class="shift-left--fluid column__primary bg--white no-pad--top no-pad--btm can-be--dark-light">
      <?php if ($carousel_type == 'small_format_inset'): ?>
        <?php include(locate_template('patterns/components/hero-carousel.php')); ?>
      <?php endif; ?>
      <?php if (!have_posts()) : ?>
        <div class="pad--primary no-pad--top no-pad--btm spacing--half text">
          <div class="alert alert-warning pad-double--top pad-half--btm">
            <?php _e('Sorry, no results were found.', 'sage'); ?>
          </div>
          <?php get_template_part('patterns/components/search-form'); ?>
        </div>
      <?php endif; ?>
      <div class="spacing--half">
        <div class="pad-half">
          <h2 class="font--tertiary--l theme--primary-text-color pad-double--top pad-half--btm">Hae seurakuntaa</h2>
          <div class="search_congreg">
            <div>
              <form method="get" onsubmit="return checkLength();">
                <p>Postinumerollasi</p>
                <input title="Zipcode" id="zipcode" class="adventistfi_congreg_zipsearch" type="number" name="zip" value="<?php echo @$_GET['zip']; ?>" required="required" />
                <button>Hae &raquo;</button>
              </form>
            </div>
            <div>
              <form action="?" method="post" id="locate">
                <p>Sijainnillasi</p>
                <input type="hidden" id="geolocation" required="required" value="<?php echo @$_POST['geolocation']; ?>" name="geolocation" />
              </form>
              <button title="GPS" onclick="return locate();">Paikanna</button>
              <p id="gettingLocationMessage" style="display: none;">Haetaan sijaintia... Odota.</p>
              <!--<p class="font--primary--xs" style="padding-top: .3rem;">Sijaintitietojen tarkkuus riippuu laitteestasi</p>-->
            </div>
          </div>
        </div>
        <?php
          $searching = isset($_GET['zip']) || isset($_POST['geolocation']);
        
          if($searching)
          {
            ?>
            <div class="pad-half">
              <a href="<?php echo "https://". $_SERVER['SERVER_NAME']; ?>/seurakunnat">&laquo; Palaa seurakuntalistaan</a>
            </div>
            <?php
          }
        ?>
        <hr>
      </div>
      <div class="with-divider grid--uniform">
        <?php 
        if (have_posts() && !$searching)
        { 
          ?>
          <?php while (have_posts()) : the_post(); ?>
            <div class="">
              <div class="spacing">
                <div class="pad-half">
                  <?php
                    $title = get_the_title();
                    $intro = get_post_meta($post->ID, 'intro', true);
                    $body = strip_tags(get_the_content());
                    $body = strip_shortcodes($body);
                    $excerpt_length = 100;
                    $image = get_post_thumbnail_id();
                    $button_url = get_the_permalink();
                    
                    $categories = array("suomenkieliset" => "Lisätietoja", "ruotsinkieliset" => "Läs mer", "englanninkieliset" => "Read more", "venajankieliset" => "Read more");
                    
                    foreach($categories as $cat => $lng) {
                      if(strpos($button_url, $cat) !== false) {
                        $lang = $lng;
                      }
                    }
                    
                    $button_text = $lang;
                    
                    $round_image = get_post_meta($post->ID, 'make_the_image_round', true);
                    $thumbnail = wp_get_attachment_image_src($image, "horiz__4x3--s")[0];
                    $thumbnail_round = wp_get_attachment_image_src($image, "square--s")[0];
                    $alt = get_post_meta($image, '_wp_attachment_image_alt', true);
                    
                    $street_address = get_post_field("address", $post);
                    $phone = get_post_field("phone", $post);
                    $website = get_post_field("website", $post);
                  ?>
                  <?php if(!isset($_GET['zip'])) include('item-block.php'); ?>
                </div>
              </div>
            </div> <!-- /.gi -->
          <?php endwhile; ?>
        <?php 
          }
          else 
          {            
            if(isset($_GET['zip']))
            {
              // Get all found congregations for the matched zip codes
              $range = zipSearchRange($_GET['zip']);
              $results = [];
              foreach($range as $zip)
              {
                $r = $zipCodes[$zip];
                if(isset($r))
                {
                  foreach($r as $res)
                    $results[] = $res;
                }
              }

              echo "<div class='pad-half'>";
              
              // Display results if any
              if(count($results) > 0)
              {                
                foreach($results as $post)
                {                  
                  $title = $post->post_title;
                  $button_url = get_permalink($post);
                  
                  $body = "Hakutulos";
                  $excerpt_length = 100;
                  
                  $categories = array("suomenkieliset" => "Lisätietoja", "ruotsinkieliset" => "Läs mer", "englanninkieliset" => "Read more", "venajankieliset" => "Read more");
                  
                  foreach($categories as $cat => $lng) 
                  {
                    if(strpos($button_url, $cat) !== false) 
                    {
                      $lang = $lng;
                    }
                  }
                  
                  $button_text = $lang;
                  
                  $street_address = get_post_field("address", $post);
                  $phone = get_post_field("phone", $post);
                  $website = get_post_field("website", $post);
                  
                  include('item-block.php');
                }                
              }
              else
              {
                ?>
                <h3>Seurakuntia ei löytynyt antamallasi postinumerolla.</h3>
                <?php 
                  $len = strlen($_GET['zip']);
                  if($len > 5 || $len < 5)
                  { ?>
                    <h4>Tarkista syöttämäsi postinumero</h4> 
                    <?php 
                  }                  
                  ?>
                <?php
              }
              echo "</div>";
            }
            elseif(isset($_POST['geolocation']))
            {
              // Get all found congregations for the matched coordinates
              $range = coordSearchRange($_POST['geolocation']);
              $results = [];
              
              for($i=0;$i<count($coords);$i++)
              {
                $c = explode(",", $_POST['geolocation']);
                
                $lat = $coords[$i]['lat'];
                $lng = $coords[$i]['lng'];
                $match = $coords[$i]['data'];
                
                if($lat >= $range['lat'][0] && $lat <= $range['lat'][1] && 
                  $lng >= $range['lng'][0] && $lng <= $range['lng'][1])
                {
                  if($c[0] > $lat)
                    $diffLat = $c[0] - $lat;
                  else
                    $diffLat = $lat - $c[0];
                  
                  $results[] = array('postIndex' => $i, 'latDiff' => $diffLat, 'lat' => $lat, 'lng' => $lng);
                }
              }
              
              // Sort congregations by distance, closest first
              uasort($results, "compare");
              
              echo "<div class='pad-half'>";
              
              // Display results if any
              if(count($results) > 0)
              {                
                foreach($results as $res)
                {
                  // post
                  $p = $coords[$res['postIndex']]['data'];
                  $title = $p->post_title;
                  
                  // taxonomy term for language group
                  $tax = "adventistfi_congregation_lang";
                  $term = get_the_terms($p, $tax);
                  $languageGroup = $term[0]->slug;
                  
                  // permalink of post
                  $button_url = "//" . $_SERVER['SERVER_NAME'] . "/seurakunnat/" . $languageGroup . "/" . $p->post_name;
                                    
                  $body = "Noin " . DistanceKm(array('lat' => $c[0], 'lng' => $c[1]), array('lat' => $res['lat'], 'lng' => $res['lng'])) . " km";
                  $excerpt_length = 100;
                  
                  $categories = array("suomenkieliset" => "Lisätietoja", "ruotsinkieliset" => "Läs mer", "englanninkieliset" => "Read more", "venajankieliset" => "Read more");
                  
                  $lang = $categories[$languageGroup];
                  
                  $button_text = $lang;
                  
                  $post_fields = get_fields($p->ID);
                  
                  $street_address = $post_fields['address'];
                  $phone = $post_fields['phone'];
                  $website = $post_fields['website'];
                  
                  include('item-block.php');
                }
              }
              else
              {
                ?>
                <h3>Seurakuntia ei löytynyt nykyisen sijaintisi perusteella.</h3>
                <?php
              }
              echo "</div>";
            }
          }
          ?>
        <?php wp_reset_query(); ?>
      </div> <!-- /.2up--at-medium -->
      <div class="space--btm">
        <?php if(!$searching) get_template_part('patterns/components/pagination'); ?>
      </div>
    </div> <!-- /.shift-left--fluid -->
    <?php get_sidebar(); ?>
  </div> <!-- /.flex-container -->
</div> <!-- /.layout-container -->
