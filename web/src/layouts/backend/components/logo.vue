<template>
    <div class="layout-logo">
        <img v-if="!config.layout.menuCollapse" class="logo-img" src="~assets/logo.png" alt="logo" />
        <div v-if="!config.layout.menuCollapse" :style="{ color: config.layout.menuActiveColor }" class="website-name">
            {{ siteConfig.site_name }}
        </div>
        <Icon
            v-if="config.layout.layoutMode != 'Streamline'"
            @click="onMenuCollapse"
            :name="config.layout.menuCollapse ? 'fa fa-indent' : 'fa fa-dedent'"
            :class="config.layout.menuCollapse ? 'unfold' : ''"
            :color="config.layout.menuActiveColor"
            size="18"
            class="fold"
        />
    </div>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import { useSiteConfig } from '/@/stores/siteConfig'
import { closeShade } from '/@/utils/pageShade'

const config = useConfig()
const siteConfig = useSiteConfig()

const onMenuCollapse = function () {
    if (config.layout.shrink && !config.layout.menuCollapse) {
        closeShade()
    }

    config.setLayout('menuCollapse', !config.layout.menuCollapse)
}
</script>

<style scoped lang="scss">
.layout-logo {
    width: 100%;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    padding: 10px;
    background: v-bind('config.layout.layoutMode != "Streamline" ? config.layout.menuTopBarBackground:"transparent"');
}
.logo-img {
    width: 28px;
}
.website-name {
    padding-left: 4px;
    font-size: var(--el-font-size-extra-large);
    font-weight: 600;
}
.fold {
    margin-left: auto;
}
.unfold {
    margin: 0 auto;
}
</style>
