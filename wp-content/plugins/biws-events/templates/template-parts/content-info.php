<!--/**
 // CPT events
 // TAXONOMY event_partner
 ///-->
<!-- start event info -->
<div class="biws-event-info-highlight wp-block-group alignfull has-accent-background-color has-background">
    <p class="biws-event-info-date has-larger-font-size">
        <?php
        // EVENT Date
        $start_date = get_post_meta(get_the_id(), 'event-start-date', true);
        $end_date = get_post_meta(get_the_id(), 'event-end-date', true);
        if ($end_date && $end_date > $start_date) {
            echo date_i18n("d. M", $start_date) . " - " . date_i18n("d. M", $end_date);
        } else {
            echo date_i18n("d. M", $start_date);
        }
        unset($start_date);
        unset($end_date);
        // EVENT Date end
        ?>
    </p>
    <?php
    // EVENT Type aka Category
    $event_types = wp_get_post_terms(get_the_id(), 'event_type');
    if ($event_types) {
    ?>
        <p class="biws-event-info-category has-large-font-size">
            <?php echo $event_types[0]->name; ?>
        </p>
    <?php
    }
    unset($event_types);
    // EVENT Type aka Category end

    // EVENT Organisator
    $event_orga = get_post_meta(get_the_id(), 'event-organizer', true);
    $event_orga_url = get_post_meta(get_the_id(), 'event-organizer-url', true);

    if ($event_orga) {
    ?>
        <p class="biws-event-info-by">
            <?php
            echo 'Präsentiert von ';
            //echo __('Presented by', 'biws-textdomain') . " ";
            if ($event_orga_url) {
            ?>
                <a class="biws-event-info-organisator" href="<?php echo $event_orga_url ?>"><?php echo $event_orga; ?></a>
            <?php
            } else {
            ?>
                <span class="biws-event-info-organisator"><?php echo $event_orga; ?></span>
            <?php
            }
            ?>
        </p>
    <?php
    }
    unset($event_orga);
    unset($event_orga_url);
    // EVENT Organisator end
    ?>
</div>
<div class="biws-event-info-basic wp-block-group alignfull has-primary-background-color has-background has-small-font-size">
    <p class="biws-event-info-section-header has-larger-font-size">
        Übersicht
        <?php //_e('Overview', 'biws-textdomain'); 
        ?>
    </p>
    <?php
    // EVENT Fee
    $event_fee = get_post_meta(get_the_id(), 'event-entry-fee', true);
    $event_fee_label = get_post_meta(get_the_id(), 'event-entry-fee-label', true);
    $event_fee_description = get_post_meta(get_the_id(), 'event-entry-fee-description', true);

    if ($event_fee || $event_fee_label || $event_fee_description) {
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="#56704c" d="M15,4A8,8 0 0,1 23,12A8,8 0 0,1 15,20A8,8 0 0,1 7,12A8,8 0 0,1 15,4M15,18A6,6 0 0,0 21,12A6,6 0 0,0 15,6A6,6 0 0,0 9,12A6,6 0 0,0 15,18M3,12C3,14.61 4.67,16.83 7,17.65V19.74C3.55,18.85 1,15.73 1,12C1,8.27 3.55,5.15 7,4.26V6.35C4.67,7.17 3,9.39 3,12Z"></path>
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <?php
                if ($event_fee || $event_fee_label) {
                ?>
                    <p class="biws-event-info-detail-fee">
                        <?php
                        if ($event_fee) {
                            echo '<span class="biws-event-info-detail-fee-value">' . $event_fee . '&#x20ac;</span>' . ($event_fee_label ? " " : "");
                        }
                        if ($event_fee_label) {
                            echo '<span class="biws-event-info-detail-fee-label">' . $event_fee_label . '</span>';
                        }
                        ?>
                    </p>
                <?php
                }
                if ($event_fee_description) {
                ?>
                    <p class="biws-event-info-detail-fee-description"><?php echo $event_fee_description; ?></p>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    unset($event_fee);
    unset($event_fee_label);
    unset($event_fee_description);
    // EVENT Fee end

    // EVENT Times
    $event_times = array();
    for ($i = 1; $i <= 4; $i++) {;
        $tmp = array(
            'time' => get_post_meta(get_the_id(), 'event-time-' . $i . '-time', true),
            'label' => get_post_meta(get_the_id(), 'event-time-' . $i . '-label', true),
        );
        if ($tmp['time'] && $tmp['label']) {
            $event_times[] = $tmp;
        }

        unset($tmp);
    }
    if ($event_times) {
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="#56704c" d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z"></path>
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <?php foreach ($event_times as $event_time) { ?>
                    <p class="biws-event-info-detail-time">
                        <span class="biws-event-info-detail-time-time"><?php echo $event_time['time']; ?></span> <span class="biws-event-info-detail-time-label"><?php echo $event_time['label']; ?></span>
                    </p>
                <?php } // end for each $event_times 
                ?>
            </div>
        </div>
    <?php
    }
    unset($event_times);
    // EVENT Times end

    // EVENT Venue
    $event_venue = wp_get_post_terms(get_the_id(), 'event_venue');
    if ($event_venue) {
        $street = get_term_meta($event_venue[0]->term_id, 'venue-street', true);
        $zip = get_term_meta($event_venue[0]->term_id, 'venue-zipcode', true);
        $city = get_term_meta($event_venue[0]->term_id, 'venue-city', true);
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="#56704c" d="M12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5M12,2A7,7 0 0,1 19,9C19,14.25 12,22 12,22C12,22 5,14.25 5,9A7,7 0 0,1 12,2M12,4A5,5 0 0,0 7,9C7,10 7,12 12,18.71C17,12 17,10 17,9A5,5 0 0,0 12,4Z"></path>
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <p class="biws-event-info-detail-label">
                    <?php echo $event_venue[0]->name; ?>
                </p>
                <?php
                if ($street || $zip || $city) {
                    $venue_string = "";
                    if ($street) {
                        $venue_string .= $street;
                        if ($zip || $city) {
                            $venue_string .= ", ";
                        }
                    }
                    if ($zip) {
                        $venue_string .= $zip;
                        if ($city) {
                            $venue_string .= " ";
                        }
                    }
                    if ($city) {
                        $venue_string .= $city;
                    }
                ?>
                    <p class="biws-event-info-detail-venue-address">
                        <?php echo $venue_string; ?>
                    </p>
                <?php
                    unset($venue_string);
                }
                ?>
            </div>
        </div>
    <?php
        unset($street);
        unset($zip);
        unset($city);
    }
    unset($event_venue);
    // EVENT Venue end

    // EVENT Disciplines
    $event_disciplines = wp_get_post_terms(get_the_id(), 'event_discipline', array(
        'meta_key'   => 'order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ));

    if ($event_disciplines) {
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="#56704c" d="M2,2V4H7V8H2V10H7C8.11,10 9,9.11 9,8V7H14V17H9V16C9,14.89 8.11,14 7,14H2V16H7V20H2V22H7C8.11,22 9,21.11 9,20V19H14C15.11,19 16,18.11 16,17V13H22V11H16V7C16,5.89 15.11,5 14,5H9V4C9,2.89 8.11,2 7,2H2Z"></path>
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <p class="biws-event-info-detail-label">
                    Disziplinen
                    <?php //_e('Disciplines', 'biws-textdomain'); 
                    ?>
                </p>
                <div class="biws-info-list biws-event-disciplines">
                    <ul>
                        <?php foreach ($event_disciplines as $event_discipline) { ?>
                            <li class="biws-event-info-detail-discipline">
                                <?php echo $event_discipline->name; ?>
                            </li>
                        <?php } // end for each $event_disciplines 
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    }
    unset($event_disciplines);
    // EVENT Disciplines end

    // EVENT Tables
    $event_tables = wp_get_post_terms(get_the_id(), 'event_table');
    if ($event_tables) {
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg width="24" height="24" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4 13H18V18C18 18.5523 18.4477 19 19 19C19.5523 19 20 18.5523 20 18V12.6667L21 12V17.5C21.8284 17.5 22.5 16.8284 22.5 16V8C22.5 7.44772 22.0523 7 21.5 7L6.47213 7.00001C6.16164 7.00001 5.85542 7.0723 5.57771 7.21115L3.10557 8.44722C2.428 8.786 2 9.47853 2 10.2361V18C2 18.5523 2.44772 19 3 19C3.55228 19 4 18.5523 4 18V13ZM17.25 10C16.8358 10 16.5 10.3358 16.5 10.75V10.75C16.5 11.1642 16.8358 11.5 17.25 11.5V11.5C17.6642 11.5 18 11.1642 18 10.75V10.75C18 10.3358 17.6642 10 17.25 10V10ZM5.25 10C4.83579 10 4.5 10.3358 4.5 10.75V10.75C4.5 11.1642 4.83579 11.5 5.25 11.5V11.5C5.66421 11.5 6 11.1642 6 10.75V10.75C6 10.3358 5.66421 10 5.25 10V10ZM9.25 10C8.83579 10 8.5 10.3358 8.5 10.75V10.75C8.5 11.1642 8.83579 11.5 9.25 11.5V11.5C9.66421 11.5 10 11.1642 10 10.75V10.75C10 10.3358 9.66421 10 9.25 10V10ZM13.25 11.5C12.8358 11.5 12.5 11.1642 12.5 10.75V10.75C12.5 10.3358 12.8358 10 13.25 10V10C13.6642 10 14 10.3358 14 10.75V10.75C14 11.1642 13.6642 11.5 13.25 11.5V11.5Z" fill="#56704C" />
                    <path d="M5.00001 17.5V14H6.50001V16C6.50001 16.8284 5.82844 17.5 5.00001 17.5Z" fill="#56704C" />
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <p class="biws-event-info-detail-label">
                    Tische
                    <?php //_e('Tables', 'biws-textdomain'); 
                    ?>
                </p>
                <div class="biws-info-list biws-event-tables">
                    <ul>
                        <?php foreach ($event_tables as $table) { ?>
                            <li class="biws-event-info-detail-table"><?php echo $table->name; ?></li>
                        <?php } // end for each $event_tables 
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    }
    unset($event_tables);
    // EVENT Tables end

    // EVENT Links
    $event_urls = array();
    for ($i = 1; $i <= 4; $i++) {;
        $tmp = array(
            'url' => get_post_meta(get_the_id(), 'event-link-' . $i . '-url', true),
            'label' => get_post_meta(get_the_id(), 'event-link-' . $i . '-label', true),
        );
        if ($tmp['url']) {
            $event_urls[] = $tmp;
        }

        unset($tmp);
    }
    if ($event_urls) {
    ?>
        <div class="biws-event-info-detail-wrapper">
            <div class="biws-event-info-detail-svg">
                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                    <path fill="#56704c" d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z"></path>
                </svg>
            </div>
            <div class="biws-event-info-detail-inner">
                <p class="biws-event-info-detail-label">
                    Links
                    <?php //_e('Links', 'biws-textdomain'); 
                    ?>
                </p>
                <div class="biws-info-list biws-event-links">
                    <ul>
                        <?php foreach ($event_urls as $event_url) { ?>
                            <li class="biws-event-info-detail-link">
                                <a href="<?php echo $event_url['url']; ?>" class="biws-event-link"><?php echo $event_url['label'] ? $event_url['label'] : $event_url['url']; ?></a>
                            </li>
                        <?php } // end for each $event_urls 
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    }
    unset($event_urls);
    // EVENT Links end

    // TAXONOMY event_tag
    // EVENT Tags
    $event_tags = wp_get_post_terms(get_the_id(), 'event_tag');
    if ($event_tags) {
    ?>
        <div class="biws-event-entry-tags">
            <?php
            foreach ($event_tags as $tag) {
                $color = get_term_meta($tag->term_id, 'tag-color', true);
            ?>
                <p class="biws-event-entry-tag" <?php if ($color) echo 'style="background:#' . $color . '!important"'; ?>><?php echo $tag->name; ?></p>
            <?php
                unset($color);
            }
            ?>
        </div>
    <?php
    }
    unset($event_tags);
    // EVENT Tags end
    ?>
</div>

<!-- end event info -->