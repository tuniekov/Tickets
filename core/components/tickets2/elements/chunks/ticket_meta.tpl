<div class="ticket-meta row" data-id="[[+id]]">
    <span class="col-md-5">
        <i class="glyphicon glyphicon-calendar"></i> [[+date_ago]]
        &nbsp;&nbsp;
        <i class="glyphicon glyphicon-user"></i> [[+fullname]]
    </span>
    <span class="col-md-2"><a href="[[~[[+section.id]]]]">
            <i class="glyphicon glyphicon-folder-open"></i> [[+section.pagetitle]]</a>
    </span>
    <span class="col-md-2">
        <span class="ticket-star[[+can_star]]">[[+stared]][[+unstared]] <span
                    class="ticket-star-count">[[+stars]]</span></span>
        &nbsp;&nbsp;
        <i class="glyphicon glyphicon-eye-open"></i> [[+views]]
    </span>
    <span class="pull-right ticket-rating[[+active]][[+inactive]]">
        <span class="vote plus[[+voted_plus]]" title="[[%ticket_like]]">
            <i class="glyphicon glyphicon-arrow-up"></i>
        </span>
        [[+can_vote]][[+cant_vote]]
        <span class="vote minus[[+voted_minus]]" title="[[%ticket_dislike]]">
            <i class="glyphicon glyphicon-arrow-down"></i>
        </span>
    </span>
</div>
[[+has_files]]

<!--tickets2_can_vote <span class="vote rating" title="[[%ticket_refrain]]"><i class="glyphicon glyphicon-minus"></i></span>-->
<!--tickets2_cant_vote <span class="rating[[+rating_positive]][[+rating_negative]]" title="[[%ticket_rating_total]] [[+rating_total]]: ↑[[+rating_plus]] [[%ticket_rating_and]] ↓[[+rating_minus]]">[[+rating]]</span>-->
<!--tickets2_active  active-->
<!--tickets2_inactive  inactive-->
<!--tickets2_voted_plus  voted-->
<!--tickets2_voted_minus  voted-->
<!--tickets2_rating_positive  positive-->
<!--tickets2_rating_negative  negative-->
<!--tickets2_has_files
<ul class="ticket-files">
    <strong>[[%ticket_uploaded_files]]:</strong>
    [[+files]]
</ul>-->
<!--tickets2_can_star  active-->
<!--tickets2_stared <i class="glyphicon glyphicon-star stared star"></i>-->
<!--tickets2_unstared <i class="glyphicon glyphicon-star unstared star"></i>-->