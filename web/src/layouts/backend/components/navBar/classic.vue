<template>
    <div class="nav-bar">
        <div v-if="config.layout.shrink && config.layout.menuCollapse" class="unfold">
            <Icon @click="onMenuCollapse" name="fa fa-indent" :color="config.layout.menuActiveColor" size="18" />
        </div>
        <NavTabs v-if="!config.layout.shrink" />
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import NavTabs from '/@/layouts/backend/components/navBar/tabs.vue'
import NavMenus from '../navMenus.vue'
import { showShade } from '/@/utils/pageShade'

const config = useConfig()

const onMenuCollapse = () => {
    showShade('ba-aside-menu-shade', () => {
        config.setLayout('menuCollapse', true)
    })
    config.setLayout('menuCollapse', false)
}
</script>

<style scoped lang="scss">
.nav-bar {
    display: flex;
    height: 50px;
    width: 100%;
    background-color: v-bind('config.layout.headerBarBackground');
    :deep(.nav-tabs) {
        display: flex;
        height: 100%;
        position: relative;
        .ba-nav-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            cursor: pointer;
            z-index: 1;
            height: 100%;
            user-select: none;
            color: v-bind('config.layout.headerBarTabColor');
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
            .close-icon {
                padding: 2px;
                margin: 2px 0 0 4px;
            }
            .close-icon:hover {
                background: var(--color-primary-sub-0);
                color: var(--color-sub-1) !important;
                border-radius: 50%;
            }
            &.active {
                color: v-bind('config.layout.headerBarTabActiveColor');
            }
            &:hover {
                background-color: v-bind('config.layout.headerBarHoverBackground');
            }
        }
        .nav-tabs-active-box {
            position: absolute;
            height: 50px;
            background-color: v-bind('config.layout.headerBarTabActiveBackground');
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
        }
    }
}
.unfold {
    align-self: center;
    padding-left: var(--main-space);
}
</style>
