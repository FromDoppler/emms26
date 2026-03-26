 <main>
   <section class="emms__hero-conference emms__hero-conference--live emms__hero-conference--live--digital-trends emms__hero-conference--chat">
     <div class="emms__container--lg">
       <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/event-live.php') ?>
       <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
     </div>
   </section>

   <div class="emms__bg-dark-gradient">
     <div class="gold-schedule">
       <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
     </div>
   </div>
   <div class="show--vip">
     <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/referral.php') ?>
   </div>
   <div class="hidden--vip centralvideo--tickets">
     <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/entry-plans.php') ?>
     <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/video-ticketing.php') ?>
     <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/vip-features.php') ?>
     <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/referral.php') ?>
   </div>
   <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/premium-content.php') ?>
   <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
 </main>
