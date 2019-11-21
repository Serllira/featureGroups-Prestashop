<section class="product-features">
    <div class="div-titulo-ficha">
          <h2 class="titulo-ficha">Technical specifications</h2>
    </div>
    <div class="data-sheet">
        {foreach from=$groups item=group}
            {if $group.name_group != false}
                <div class="container-group col-5" id="container-{$group.name_group}">
                    <div id="container-title-{$group.name_group}" >
                        <h2 class="group-title">{$group.name_group}</h2>
                    </div>
                    <div class="container-features" id="container-features-{$group.name_group}">
                    {foreach from=$features_group item=feature}
                        {if $group.name_group == $feature.name_group}
                            <div class="feature" id="feature-{$feature.id_feature}">
                            {if $feature.feature_image != false}
                                <img class="feature-image" src={$feature.feature_image}>
                            {/if}
                                <span class="feature-name" >{$feature.name_feature}</span>
                                <span class="feature-value">{$feature.value_feature}</span>
                            </div>
                            <hr>
                        {/if}
                    {/foreach}
                    </div>
                </div>
            {/if}
            {if $group.name_group == false or $group.name_group == ""}
                <div class="container-group col-5" id="container-{$unassignedGroup}">
                    <div id="container-title-{$unassignedGroup}" >
                        <h2 class="group-title">{$unassignedGroup}</h2>
                    </div>
                    <div class="container-features" id="container-features-{$unassignedGroup}">
                    {foreach from=$features_group item=feature}
                        {if $group.name_group == $feature.name_group}
                            <div class="feature" id="feature-{$feature.id_feature}">
                            {if $feature.feature_image != false}
                                <img class="feature-image" src={$feature.feature_image}>
                                <span class="feature-name" >{$feature.name_feature}</span>
                                <span class="feature-value">{$feature.value_feature}</span>
                            {/if}
                            {if $feature.feature_image == false}
                                <span class="no-image"></span>
                                <span class="feature-name" >{$feature.name_feature}</span>
                                <span class="feature-value">{$feature.value_feature}</span>
                            {/if}
                            </div>
                            <hr>
                        {/if}
                    {/foreach}
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
</section>

{*
<section class="product-features">
    <div class="div-titulo-ficha">
          <h2 class="titulo-ficha">Technical specifications</h2>
    </div>
    <div class="data-sheet">
        {foreach from=$groups item=group}
            <div id="container-{$group.name_group}">
                <div id="container-title-{$group.name_group}" >
                    <h2 style="color: red">{$group.name_group}</h2>
                </div>
                <div id="container-features-{$group.name_group}">
                {foreach from=$features_group item=feature}

                    {if $group.name_group == $feature.name_group}
                                        {$group.name_group}:{$feature.name_group}
                        <div id="feature-{$feature.id_feature}">
                            <span class="aTitle col-md-4">{$feature.name_feature} : {$feature.value_feature}</span>
                        </div>
                    {/if}

                {/foreach}
                </div>
            </div>

        {/foreach}
    </div>
</section>
*}