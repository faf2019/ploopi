<?php

  // INCLUDE
  require ("rss.genesis.php");
  
  // STARTS
  if ($_GET['rssversion']) {
        $rss = new rssGenesis($_GET['rssversion']);
  }
  else
  {
        $rss = new rssGenesis();
  }
  
  // CHANNEL
  $rss->setChannel (
                                  null, // Title
                                  null, // Link
                                  null, // Description
                                  null, // Language
                                  null, // Copyright
                                  null, // Managing Editor
                                  null, // WebMaster
                                  null, // Rating
                                  "auto", // PubDate
                                  "auto", // Last Build Date
                                  "Test", // Category
                                  null, // Docs
                                  null, // Time to Live
                                  null, // Skip Days
                                  null // Skip Hours
                                );
                                
  // IMAGE
  $rss->setImage (
                               null, // Title
                               null, // Source
                               null, // Link
                               "auto", // Width
                               "auto", // Height
                               null // Description
                             );
  
  // ITEM
  $rss->addItem (
                             "First Link", // Title
                             "http://www.google.com/", // Link
                             "First description...", // Description
                             "01/01/2006", //Publication Date
                             "Test" // Category                          
                           );
  
  // ITEM
  $rss->addItem (
                             "Second Link", // Title
                             "http://www.php.net/", // Link
                             "Second description...", // Description
                             "02/14/2006", //Publication Date
                             "Test" // Category             
                           );
  
  // ITEM
  $rss->addItem (
                             "Third Link", // Title
                             "http://rssgenesis.sourceforge.net/", // Link
                             "Third description...", // Description
                             "03/25/2006", //Publication Date
                             "Test" // Category             
                           );
  
  // INPUT
  $rss->setInput (
                             null, // Title
                             null, // Description
                             null, // Name
                             null // Link
                           );
  
  // FINISH
  $rss->createFile ("my.rss");

?>