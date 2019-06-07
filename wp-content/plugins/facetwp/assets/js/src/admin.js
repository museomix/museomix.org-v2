(($ => {

    $(() => {
        init_vue();
        init_jquery();
    });

    function init_vue() {

        Vue.config.devtools = true;

        Vue.component('multiselect', VueMultiselect.default);

        Vue.filter('i18n', str => FWP.__(str));

        // Defaults mixin
        const builder_defaults = {
            methods: {
                defaultLayout() {
                    return {
                        items: [this.defaultRow()],
                        settings: this.getDefaultSettings('layout')
                    };
                },
                defaultRow() {
                    return {
                        type: 'row',
                        items: [this.defaultCol()],
                        settings: this.getDefaultSettings('row')
                    };
                },
                defaultCol() {
                    return {
                        type: 'col',
                        items: [],
                        settings: this.getDefaultSettings('col')
                    };
                },
                defaultItem(source) {
                    return {
                        type: 'item',
                        source,
                        settings: this.getDefaultSettings('item', source)
                    };
                },
                mergeSettings(settings, type, source) {
                    let defaults = this.getDefaultSettings(type, source);
                    let default_keys = Object.keys(defaults);
                    let setting_keys = Object.keys(settings);

                    // Automatically inject new settings
                    let missing_keys = default_keys.filter(name => !setting_keys.includes(name));

                    missing_keys.forEach((name, index) => {
                        Vue.set(settings, name, defaults[name]);
                    });

                    return settings;
                },
                cloneObj(obj) {
                    return JSON.parse(JSON.stringify(obj));
                },
                getSettingsMeta() {
                    let settings = {
                        num_columns: {
                            type: 'number',
                            title: FWP.__('Results per row'),
                            defaultValue: 1
                        },
                        grid_gap: {
                            type: 'number',
                            title: FWP.__('Grid gap'),
                            defaultValue: 10
                        },
                        text_style: {
                            type: 'text-style',
                            title: FWP.__('Text style'),
                            tab: 'style',
                            defaultValue: {
                                align: '',
                                bold: false,
                                italic: false
                            }
                        },
                        text_color: {
                            type: 'color',
                            title: FWP.__('Text color'),
                            tab: 'style'
                        },
                        font_size: {
                            type: 'slider',
                            title: FWP.__('Font size'),
                            tab: 'style',
                            defaultValue: {
                                unit: 'px',
                                size: 0
                            }
                        },
                        background_color: {
                            type: 'color',
                            title: FWP.__('Background color'),
                            tab: 'style'
                        },
                        border: {
                            type: 'border',
                            title: FWP.__('Border'),
                            tab: 'style',
                            defaultValue: {
                                style: 'none',
                                color: '',
                                width: {
                                    unit: 'px',
                                    top: 0,
                                    right: 0,
                                    bottom: 0,
                                    left: 0
                                }
                            },
                            children: {
                                style: {
                                    type: 'select',
                                    title: FWP.__('Border style'),
                                    choices: {
                                        'none': FWP.__('None'),
                                        'solid': FWP.__('Solid'),
                                        'dashed': FWP.__('Dashed'),
                                        'dotted': FWP.__('Dotted'),
                                        'double': FWP.__('Double')
                                    }
                                },
                                color: {
                                    type: 'color',
                                    title: FWP.__('Border color')
                                },
                                width: {
                                    type: 'utrbl',
                                    title: FWP.__('Border width')
                                }
                            }
                        },
                        button_text: {
                            type: 'text',
                            title: FWP.__('Button text')
                        },
                        button_text_color: {
                            type: 'color',
                            title: FWP.__('Button text color')
                        },
                        button_color: {
                            type: 'color',
                            title: FWP.__('Button color')
                        },
                        button_padding: {
                            type: 'utrbl',
                            title: FWP.__('Button padding'),
                            defaultValue: {
                                unit: 'px',
                                top: 0,
                                right: 0,
                                bottom: 0,
                                left: 0
                            }
                        },
                        separator: {
                            type: 'text',
                            title: FWP.__('Separator'),
                            defaultValue: ', '
                        },
                        custom_css: {
                            type: 'textarea',
                            title: FWP.__('Custom CSS'),
                            tab: 'style'
                        },
                        grid_template_columns: {
                            type: 'text',
                            title: FWP.__('Column widths'),
                            defaultValue: '1fr'
                        },
                        content: {
                            type: 'textarea',
                            title: FWP.__('Content')
                        },
                        image_size: {
                            type: 'select',
                            title: FWP.__('Image size'),
                            defaultValue: 'thumbnail',
                            choices: FWP.image_sizes,
                            v_show: [
                                { type: 'source', value: 'featured_image' }
                            ]
                        },
                        author_field: {
                            type: 'select',
                            title: FWP.__('Author field'),
                            defaultValue: 'display_name',
                            choices: {
                                'display_name': FWP.__('Display name'),
                                'user_login': FWP.__('User login'),
                                'ID': FWP.__('User ID')
                            }
                        },
                        field_type: {
                            type: 'select',
                            title: FWP.__('Field type'),
                            defaultValue: 'text',
                            choices: {
                                'text': 'Text',
                                'date': 'Date',
                                'number': 'Number'
                            }
                        },
                        date_format: {
                            type: 'text',
                            title: FWP.__('Date format'),
                            placeholder: 'F j, Y',
                            v_show: [
                                { type: 'field_type', value: 'date' },
                                { type: 'source', value: 'post_date' },
                                { type: 'source', value: 'post_modified' }
                            ]
                        },
                        input_format: {
                            type: 'text',
                            title: FWP.__('Input format'),
                            placeholder: 'Y-m-d',
                            v_show: [
                                { type: 'field_type', value: 'date' },
                                { type: 'source', value: 'post_date' },
                                { type: 'source', value: 'post_modified' }
                            ]
                        },
                        number_format: {
                            type: 'select',
                            title: FWP.__('Number format'),
                            choices: {
                                '': FWP.__('None'),
                                'n': '1234',
                                'n.n': '1234.5',
                                'n.nn': '1234.56',
                                'n,n': '1,234',
                                'n,n.n': '1,234.5',
                                'n,n.nn': '1,234.56'
                            },
                            v_show: [
                                { type: 'field_type', value: 'number' }
                            ]
                        },
                        link: {
                            type: 'link',
                            title: FWP.__('Link'),
                            defaultValue: {
                                type: 'none',
                                href: '',
                                target: ''
                            },
                            children: {
                                type: {
                                    type: 'select',
                                    title: FWP.__('Link type'),
                                    choices: {
                                        'none': FWP.__('None'),
                                        'post': FWP.__('Post URL'),
                                        'custom': FWP.__('Custom URL')
                                    }
                                }
                            }
                        },
                        prefix: {
                            type: 'text',
                            title: FWP.__('Prefix')
                        },
                        suffix: {
                            type: 'text',
                            title: FWP.__('Suffix')
                        },
                        is_hidden: {
                            type: 'checkbox',
                            defaultValue: false,
                            suffix: FWP.__('Hide item?')
                        },
                        padding: {
                            type: 'utrbl',
                            title: FWP.__('Padding'),
                            defaultValue: {
                                unit: 'px',
                                top: 0,
                                right: 0,
                                bottom: 0,
                                left: 0
                            },
                            tab: 'style'
                        },
                        name: {
                            type: 'text',
                            title: FWP.__('Name')
                        },
                        css_class: {
                            type: 'text',
                            title: FWP.__('CSS class'),
                            tab: 'style'
                        }
                    };

                    settings.button_border = this.cloneObj(settings.border);
                    settings.button_border.title = FWP.__('Button border');
                    settings.button_border.tab = 'content';

                    settings.term_link = this.cloneObj(settings.link);
                    settings.term_link.children.type.choices = {
                        'none': FWP.__('None'),
                        'term': FWP.__('Term URL'),
                        'custom': FWP.__('Custom URL')
                    };

                    return settings;
                },
                getDefaultFields(type, source) {
                    let fields = [];

                    if ('layout' == type) {
                        fields.push('num_columns', 'grid_gap');
                    }

                    if ('row' == type) {
                        fields.push('grid_template_columns');
                    }

                    if ('item' == type) {
                        if ('html' == source) {
                            fields.push('content');
                        }
                        if ('featured_image' == source) {
                            fields.push('image_size', 'link');
                        }
                        if ('button' == source) {
                            fields.push('button_text', 'button_text_color', 'button_color', 'button_padding', 'button_border', 'link');
                        }
                        if ('post_date' == source || 'post_modified' == source) {
                            fields.push('date_format');
                        }
                        if ('post_title' == source) {
                            fields.push('link');
                        }
                        if ('post_author' == source) {
                            fields.push('author_field');
                        }
                        if (0 === source.indexOf('cf/')) {
                            fields.push('field_type', 'date_format', 'input_format', 'number_format', 'link');
                        }
                        if (0 === source.indexOf('woo/')) {
                            fields.push('field_type', 'date_format', 'input_format', 'number_format');
                        }
                        if (0 === source.indexOf('tax/')) {
                            fields.push('separator', 'term_link');
                        }
                        if (!['html', 'button', 'featured_image'].includes(source)) {
                            fields.push('prefix', 'suffix');
                        }
                    }

                    fields.push('border', 'background_color', 'padding', 'text_color', 'text_style', 'font_size', 'name', 'css_class');

                    if ('layout' == type) {
                        fields.push('custom_css');
                    }

                    if ('item' == type) {
                        fields.push('is_hidden');
                    }

                    return fields;
                },
                getDefaultSettings(type, source) {
                    let settings = {};
                    let settings_meta = this.getSettingsMeta();
                    let fields = this.getDefaultFields(type, source);

                    fields.forEach(name => {
                        let defaultValue = settings_meta[name].defaultValue || '';

                        if ('name' == name) {
                            defaultValue = 'el-' + Math.random().toString(36).substring(7);
                        }

                        settings[name] = defaultValue;
                    });

                    return settings;
                }
            }
        };

        /* ================ query builder ================ */

        Vue.component('query-builder', {
            props: {
                query_obj: {
                    type: Object,
                    required: true
                },
                template: {
                    type: Object,
                    required: true
                }
            },
            template: `
            <div class="qb-wrap">
                <div class="side-link">
                    <a href="javascript:;" @click="$root.getQueryArgs(template)">{{ 'Convert to query args' | i18n }}</a>
                </div>

                <div>
                    {{ 'Fetch' | i18n }}
                    <multiselect
                        v-model="query_obj.post_type"
                        :multiple="true"
                        :searchable="false"
                        :options="$root.query_data.post_types"
                        :close-on-select="false"
                        :show-labels="false"
                        label="label"
                        track-by="value"
                        placeholder="All post types">
                    </multiselect>
                    {{ 'and show' | i18n }}
                    <input type="number" v-model.number="query_obj.posts_per_page" class="qb-posts-per-page" />
                    {{ 'per page' | i18n }}
                </div>

                <div class="qb-condition"
                    v-show="query_obj.orderby.length">
                    {{ 'Sort by' | i18n }}
                </div>

                <div v-for="(row, index) in query_obj.orderby" class="qb-condition">
                    <fselect :row="row">
                        <optgroup label="Posts">
                            <option value="ID">ID</option>
                            <option value="title">{{ 'Post Title' | i18n }}</option>
                            <option value="name">{{ 'Post Name' | i18n }}</option>
                            <option value="type">{{ 'Post Type' | i18n }}</option>
                            <option value="date">{{ 'Post Date' | i18n }}</option>
                            <option value="modified">{{ 'Post Modified' | i18n }}</option>
                            <option value="menu_order">{{ 'Menu Order' | i18n }}</option>
                            <option value="post__in">post__in</option>
                        </optgroup>
                        <optgroup label="Custom Fields">
                            <option v-for="(label, name) in $root.data_sources.custom_fields.choices" :value="name">{{ label }}</option>
                        </optgroup>
                    </fselect>
                    <select v-model="row.type" v-show="row.key.substr(0, 3) == 'cf/'" class="qb-type">
                        <option value="CHAR">TEXT</option>
                        <option value="NUMERIC">NUMERIC</option>
                    </select>
                    <select v-model="row.order" class="qb-order">
                        <option value="ASC">ASC</option>
                        <option value="DESC">DESC</option>
                    </select>
                    <span @click="deleteSortCriteria(index)" class="qb-remove">
                        <i class="fas fa-minus-circle"></i>
                    </span>
                </div>

                <div class="qb-condition"
                    v-show="query_obj.filters.length">
                    {{ 'Narrow results by' | i18n }}
                </div>

                <div v-for="(row, index) in query_obj.filters" class="qb-condition">
                    <fselect :row="row">
                        <optgroup v-for="data in $root.query_data.filter_by" :label="data.label">
                            <option v-for="(label, name) in data.choices" :value="name" v-html="label"></option>
                        </optgroup>
                    </fselect>

                    <select v-model="row.type" v-show="row.key.substr(0, 3) == 'cf/'" class="qb-type">
                        <option value="CHAR">TEXT</option>
                        <option value="NUMERIC">NUMERIC</option>
                    </select>

                    <select v-model="row.compare" class="qb-compare">
                        <option v-if="showCompare('=', row)" value="=">=</option>
                        <option v-if="showCompare('!=', row)" value="!=">!=</option>
                        <option v-if="showCompare('>', row)" value=">">&gt;</option>
                        <option v-if="showCompare('>=', row)" value=">=">&gt;=</option>
                        <option v-if="showCompare('<', row)" value="<">&lt;</option>
                        <option v-if="showCompare('<=', row)" value="<=">&lt;=</option>
                        <option v-if="showCompare('IN', row)" value="IN">IN</option>
                        <option v-if="showCompare('NOT IN', row)" value="NOT IN">NOT IN</option>
                        <option v-if="showCompare('EXISTS', row)" value="EXISTS">EXISTS</option>
                        <option v-if="showCompare('NOT EXISTS', row)" value="NOT EXISTS">NOT EXISTS</option>
                    </select>

                    <multiselect
                        v-model="row.value"
                        v-show="row.compare != 'EXISTS' && row.compare != 'NOT EXISTS'"
                        v-on:tag="addTag($event, row.value)"
                        :multiple="true"
                        :taggable="true"
                        :show-labels="false"
                        :options="[]"
                        :closeOnSelect="false"
                        :placeholder="getPlaceholder(row)"
                        tag-placeholder="Hit Enter">
                    </multiselect>
                    <span @click="deleteFilterCriteria(index)" class="qb-remove">
                        <i class="fas fa-minus-circle"></i>
                    </span>
                </div>

                <div class="qb-add">
                    <button class="button" @click="addSortCriteria">{{ 'Add sort' | i18n }}</button>
                    <button class="button" @click="addFilterCriteria">{{ 'Add filter' | i18n }}</button>
                </div>
            </div>
            `,
            methods: {
                addTag(newTag, value) {
                    value.push(newTag);
                },
                getPlaceholder({key}) {
                    return ('tax/' == key.substr(0, 4)) ? FWP.__('Enter term slugs') : FWP.__('Enter values');
                },
                showCompare(option, {key}) {
                    if ('tax/' == key.substr(0, 4)) {
                        if (!['IN', 'NOT IN', 'EXISTS', 'NOT EXISTS'].includes(option)) {
                            return false;
                        }
                    }
                    if (['ID', 'post_author', 'post_status', 'post_name'].includes(key)) {
                        if (option != 'IN' && option != 'NOT IN') {
                            return false;
                        }
                    }
                    if ('post_date' == key || 'post_modified' == key) {
                        if (!['>', '>=', '<', '<='].includes(option)) {
                            return false;
                        }
                    }
                    return true;
                },
                addSortCriteria() {
                    this.query_obj.orderby.push({
                        key: 'title',
                        order: 'ASC',
                        type: 'CHAR'
                    });
                },
                addFilterCriteria() {
                    this.query_obj.filters.push({
                        key: 'ID',
                        value: [],
                        compare: 'IN',
                        type: 'CHAR'
                    });
                },
                deleteSortCriteria(index) {
                    Vue.delete(this.query_obj.orderby, index);
                },
                deleteFilterCriteria(index) {
                    Vue.delete(this.query_obj.filters, index);
                }
            }
        });

        Vue.component('fselect', {
            props: ['row'],
            template: `
            <select :id="rand" v-model="row.key" class="qb-object">
                <slot></slot>
            </select>
            `,
            methods: {
                fSelectChanged(event, $wrap) {

                    // only update this current instance
                    if (0 < $($wrap).find('#' + this.rand).length) {
                        this.row.key = this.$el.value;
                    }
                }
            },
            created() {

                // create a random ID for each fSelect instance
                this.rand = 'fs-' + Math.random().toString(36).substring(7);
            },
            mounted() {
                $(this.$el).fSelect();
                $(document).on('fs:changed', this.fSelectChanged);
            },
            beforeDestroy() {
                $(document).off('fs:changed', this.fSelectChanged);
            }
        });

        /* ================ layout builder ================ */


        Vue.component('builder', {
            props: {
                layout: Object
            },
            template: `
            <div class="builder-wrap">
                <div class="builder-canvas-wrap">
                    <div class="builder-canvas">
                        <div class="builder-edge"></div>
                        <div class="builder-edge vertical"></div>
                        <draggable :list="layout.items" handle=".builder-row-actions.not-child">
                            <builder-row
                                v-for="(row, index) in layout.items"
                                :row="row"
                                :rows="layout.items"
                                :index="index"
                                :key="index">
                            </builder-row>
                        </draggable>
                    </div>
                    <div class="builder-intro">
                        In the above canvas, build the layout for an individual result.<br><br>
                        Then in <strong>Layout</strong>, choose the number of results per row.
                    </div>
                </div>
                <builder-settings :layout="layout"></builder-settings>
            </div>
            `
        });

        Vue.component('setting-wrap', {
            mixins: [builder_defaults],
            props: ['settings', 'name', 'source', 'tab'],
            template: `
            <div class="builder-setting" v-show="isVisible">
                <div v-html="title"></div>
                <component :is="getSettingComponent" v-bind="$props" :meta="meta"></component>
            </div>
            `,
            computed: {
                getSettingComponent() {
                    return 'setting-' + this.type;
                },
                isVisible() {
                    let ret = true;
                    let self = this;

                    if ('undefined' === typeof this.meta.tab) {
                        this.meta.tab = 'content';
                    }

                    if (this.meta.tab !== this.tab) {
                        ret = false;
                    }
                    else if ('undefined' !== typeof this.meta.v_show) {
                        ret = false;
                        this.meta.v_show.forEach((cond, index) => {
                            let type = cond.type;
                            let setting_val = ('source' == type) ? self[type] : self.settings[type];
                            let cond_value = cond.value || '';
                            let cond_compare = cond.compare || '==';
                            let is_match = ('==' == cond_compare)
                                ? setting_val == cond_value
                                : setting_val != cond_value;

                            if (is_match) {
                                ret = true;
                            }
                        });
                    }

                    return ret;
                }
            },
            created() {
                this.settings_meta = this.getSettingsMeta();
                this.meta = this.settings_meta[this.name];
                this.type = this.meta.type;
                this.title = this.meta.title;
            }
        });

        Vue.component('setting-text', {
            props: ['settings', 'name', 'meta'],
            template: '<input type="text" v-model="settings[name]" :placeholder="meta.placeholder" />'
        });

        Vue.component('setting-number', {
            props: ['settings', 'name', 'meta'],
            template: '<input type="number" v-model.number="settings[name]" :placeholder="meta.placeholder" />'
        });

        Vue.component('setting-textarea', {
            props: ['settings', 'name', 'meta'],
            template: '<textarea v-model="settings[name]"></textarea>'
        });

        Vue.component('setting-slider', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div>
                <input type="range" min="0" max="80" step="1" v-model.number="settings[name].size" />
                <span v-html="fontSizeLabel" style="vertical-align:top"></span>
            </div>
            `,
            computed: {
                fontSizeLabel() {
                    let val = this.settings[this.name];
                    return (0 === val.size) ? 'none' : val.size + val.unit;
                }
            }
        });

        Vue.component('setting-color', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div class="color-wrap">
                <div class="color-canvas">
                    <span class="color-preview"></span>
                    <input type="text" class="color-input" v-model="settings[name]" />
                </div>
                <button class="color-clear">{{ 'Clear' | i18n }}</button>
            </div>`,
            mounted() {
                let self = this;
                let $canvas = self.$el.getElementsByClassName('color-canvas')[0];
                let $preview = self.$el.getElementsByClassName('color-preview')[0];
                let $input = self.$el.getElementsByClassName('color-input')[0];
                let $clear = self.$el.getElementsByClassName('color-clear')[0];
                $preview.style.backgroundColor = $input.value;

                let picker = new Picker({
                    parent: $canvas,
                    popup: 'left',
                    alpha: false,
                    onDone(color) {
                        let hex = color.hex().substr(0, 7);
                        self.settings[self.name] = hex;
                        $preview.style.backgroundColor = hex;
                    }
                });

                picker.onOpen = function(color) {
                    picker.setColor($input.value);
                };

                $clear.addEventListener('click', function() {
                    self.settings[self.name] = '';
                    $preview.style.backgroundColor = '';
                });
            }
        });

        Vue.component('setting-link', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div>
                <setting-select
                    :settings="settings[name]"
                    name="type"
                    :meta="meta.children.type">
                </setting-select>

                <div v-show="settings[name].type == 'custom'">
                    <input
                        type="text"
                        v-model="settings[name].href"
                        placeholder="https://"
                    />
                </div>
                <div v-show="settings[name].type != 'none'">
                    <input
                        type="checkbox"
                        v-model="settings[name].target"
                        true-value="_blank"
                        false-value=""
                    />
                    {{ 'Open in new tab?' | i18n }}
                </div>
            </div>
            `
        });

        Vue.component('setting-border', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div>
                <setting-select
                    :settings="settings[name]"
                    name="style"
                    :meta="meta.children.style">
                </setting-select>

                <div v-show="settings[name].style != 'none'">
                    <div v-html="meta.children.color.title" style="margin-top:10px"></div>

                    <setting-color
                        :settings="settings[name]"
                        name="color"
                        :meta="meta.children.color">
                    </setting-color>

                    <div v-html="meta.children.width.title" style="margin-top:10px"></div>

                    <setting-utrbl
                        :settings="settings[name]"
                        name="width"
                        :meta="meta.children.width">
                    </setting-utrbl>
                </div>
            </div>
            `
        });

        Vue.component('setting-checkbox', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div>
                <input type="checkbox" v-model="settings[name]" /> {{ meta.suffix }}
            </div>
            `
        });

        Vue.component('setting-select', {
            props: ['settings', 'name', 'meta'],
            template: `
            <select v-model="settings[name]">
                <option v-for="(label, value) in meta.choices" :value="value">{{ label }}</option>
            </select>
            `
        });

        Vue.component('setting-utrbl', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div>
                <div class="utrbl utrbl-unit"><input type="text" v-model="settings[name].unit" /><span>unit</span></div>
                <div class="utrbl"><input type="number" v-model.number="settings[name].top" /><span>top</span></div>
                <div class="utrbl"><input type="number" v-model.number="settings[name].right" /><span>right</span></div>
                <div class="utrbl"><input type="number" v-model.number="settings[name].bottom" /><span>bottom</span></div>
                <div class="utrbl"><input type="number" v-model.number="settings[name].left" /><span>left</span></div>
            </div>
            `
        });

        Vue.component('setting-text-style', {
            props: ['settings', 'name', 'meta'],
            template: `
            <div class="text-style-icons">
                <span @click="toggleChoice('align', 'left')" :class="{ active: isActive('align', 'left') }"><i class="fas fa-align-left"></i></span>
                <span @click="toggleChoice('align', 'center')" :class="{ active: isActive('align', 'center') }"><i class="fas fa-align-center"></i></span>
                <span @click="toggleChoice('align', 'right')" :class="{ active: isActive('align', 'right') }"><i class="fas fa-align-right"></i></span>
                <span @click="toggleChoice('bold')" :class="{ active: isActive('bold') }"><i class="fas fa-bold"></i></span>
                <span @click="toggleChoice('italic')" :class="{ active: isActive('italic') }"><i class="fas fa-italic"></i></span>
            </div>
            `,
            methods: {
                toggleChoice(opt, val) {
                    let old_val = this.settings[this.name][opt];

                    if ('undefined' !== typeof val) {
                        this.settings[this.name][opt] = (val !== old_val) ? val : '';
                    }
                    else {
                        this.settings[this.name][opt] = ! old_val;
                    }
                },
                isActive(opt, val) {
                    let new_val = ('undefined' !== typeof val) ? val : true;
                    return this.settings[this.name][opt] === new_val;
                }
            }
        });

        Vue.component('builder-settings', {
            mixins: [builder_defaults],
            props: {
                layout: Object
            },
            data() {
                return {
                    title: '',
                    type: 'layout',
                    settings: this.layout.settings,
                    source: '',
                    active_tab: 'content'
                }
            },
            template: `
            <div class="builder-settings-wrap">
                <h3>
                    <div v-show="this.title" class="builder-crumb">
                        <a href="javascript:;" @click="$root.$emit('edit-layout')">{{ 'Layout' | i18n }}</a> &raquo;
                    </div>
                    {{ settingTitle }}
                </h3>
                <div class="builder-settings">
                    <div class="template-tabs">
                        <span @click="setActiveTab('content')" :class="isActiveTab('content')">{{ 'Content' | i18n }}</span>
                        <span @click="setActiveTab('style')" :class="isActiveTab('style')">{{ 'Style' | i18n }}</span>
                    </div>
                    <setting-wrap
                        v-for="name in settingsFields"
                        :settings="settings"
                        :name="name"
                        :source="source"
                        :tab="active_tab"
                        :key="uniqueKey()">
                    </setting-wrap>
                </div>
            </div>
            `,
            computed: {
                settingTitle() {
                    return ('' === this.title) ? FWP.__('Layout') : this.title;
                },
                settingsFields() {
                    return this.getDefaultFields(this.type, this.source);
                }
            },
            methods: {
                uniqueKey() {
                    // method to prevent caching
                    return Math.floor(Math.random() * 999999);
                },
                isActiveTab(which) {
                    return (this.active_tab === which) ? 'active' : '';
                },
                setActiveTab(which) {
                    this.active_tab = which;
                }
            },
            created() {
                let self = this;

                this.$root.$on('edit-layout', () => {
                    self.title = '';
                    self.type = 'layout';
                    self.settings = self.mergeSettings(self.layout.settings, self.type);
                    self.source = '';
                });

                this.$root.$on('edit-row', ({settings}, num) => {
                    self.title = FWP.__('Row') + ' ' + num;
                    self.type = 'row';
                    self.settings = self.mergeSettings(settings, self.type);
                    self.source = '';
                });

                this.$root.$on('edit-col', ({settings}, num) => {
                    self.title = FWP.__('Column') + ' ' + num;
                    self.type = 'col';
                    self.settings = self.mergeSettings(settings, self.type);
                    self.source = '';
                });

                this.$root.$on('edit-item', ({source, settings}) => {
                    self.title = self.$root.layout_data[source];
                    self.type = 'item';
                    self.settings = self.mergeSettings(settings, self.type, source);
                    self.source = source;
                });
            }
        });

        Vue.component('builder-row', {
            mixins: [builder_defaults],
            props: {
                row: Object,
                rows: Array,
                index: Number,
                is_child: Boolean
            },
            template: `
            <div class="builder-row">
                <div class="builder-row-actions" :class="classIsChild">
                    <span @click="editRow" title="Edit row"><i class="fas fa-cog"></i></span>
                    <span @click="addCol" title="Add columm"><i class="fas fa-columns"></i></span>
                    <span @click="addRow" title="Add row"><i class="fas fa-plus"></i></span>
                    <span @click="deleteRow" title="Delete row"><i class="fas fa-times"></i></span>
                </div>
                <div class="builder-row-inner" :style="{ gridTemplateColumns: row.settings.grid_template_columns }">
                    <builder-col
                        v-for="(col, index) in row.items"
                        :col="col"
                        :cols="row.items"
                        :index="index"
                        :key="index">
                    </builder-col>
                </div>
            </div>
            `,
            computed: {
                classIsChild() {
                    return this.is_child ? 'is-child' : 'not-child';
                }
            },
            methods: {
                addRow() {
                    this.rows.splice(this.index + 1, 0, this.defaultRow());

                    if (1 < this.rows.length) {
                        this.$root.$emit('edit-row', this.rows[this.index + 1], this.index + 2);
                    }
                    else {
                        this.$root.$emit('edit-layout');
                    }
                },
                addCol() {
                    let len = this.row.items.push(this.defaultCol());
                    this.$root.$emit('edit-col', this.row.items[len - 1], len);

                    let grid_str = '1fr '.repeat(this.row.items.length).trim();
                    this.row.settings.grid_template_columns = grid_str;
                },
                editRow() {
                    this.$root.$emit('edit-row', this.row, this.index + 1);
                },
                deleteRow() {
                    Vue.delete(this.rows, this.index);
                    this.$root.$emit('edit-layout');

                    // Add default row
                    if (this.rows.length < 1) {
                        if (! this.is_child) {
                            this.addRow();
                        }
                    }
                }
            }
        });

        Vue.component('builder-col', {
            mixins: [builder_defaults, VueClickaway.mixin],
            props: {
                col: Object,
                cols: Array,
                index: Number
            },
            data() {
                return {
                    adding_item: false
                }
            },
            template: `
            <div class="builder-col">
                <col-resizer :cols="cols" :index="index" v-show="index < (cols.length - 1)"></col-resizer>
                <popover :col="col" v-if="adding_item" v-on-clickaway="away"></popover>
                <div class="builder-col-actions">
                    <span @click="editCol" title="Edit columm"><i class="fas fa-cog"></i></span>
                    <span @click="deleteCol" title="Delete column"><i class="fas fa-times"></i></span>
                </div>
                <div class="builder-col-inner" :class="[ !col.items.length ? 'empty-col' : '' ]">
                    <draggable v-model="col.items" handle=".item-drag" group="drag-across-columns" class="draggable">
                        <div v-for="(item, index) in col.items" :key="index">
                        <builder-item
                            v-if="item.type != 'row'"
                            :item="item"
                            :items="col.items"
                            :index="index">
                        </builder-item>
                        <builder-row
                            v-if="item.type == 'row'"
                            :row="item"
                            :rows="col.items"
                            :index="index"
                            :is_child="true">
                        </builder-row>
                        </div>
                        <div class="builder-empty-view" @click="addItem">
                            <div class="builder-first-add">+</div>
                        </div>
                    </draggable>
                </div>
            </div>
            `,
            methods: {
                addItem() {
                    this.adding_item = ! this.adding_item;
                },
                editCol() {
                    this.$root.$emit('edit-col', this.col, this.index + 1);
                    this.adding_item = false;
                },
                deleteCol() {
                    // Remove the column
                    this.cols.splice(this.index, 1);

                    // Show the "Layout" settings
                    this.$root.$emit('edit-layout');

                    // Add default column
                    if (this.cols.length < 1) {
                        this.cols.push(this.defaultCol());
                    }

                    // Adjust the row's `grid_template_columns` string
                    let grid_str = '1fr '.repeat(this.cols.length).trim();
                    this.$parent.row.settings.grid_template_columns = grid_str;
                },
                away() {
                    this.adding_item = false;
                }
            }
        });

        Vue.component('col-resizer', {
            props: {
                cols: Array,
                index: Number
            },
            data() {
                return {
                    isResizing: false
                };
            },
            template: '<div :class="classNames" @mousedown="onMouseDown"></div>',
            computed: {
                classNames() {
                    return [
                        'resizer',
                        this.isResizing ? 'is-resizing' : ''
                    ];
                }
            },
            methods: {
                onMouseDown({ target: resizer, pageX: initialPageX, pageY: initialPageY }) {
                    if (! resizer.classList.contains('resizer')) {
                        return;
                    }

                    let self = this;
                    let pane = resizer.parentElement;
                    let row_inner = pane.parentElement;
                    let initialPaneWidth = pane.offsetWidth;

                    const resize = (initialSize, offset = 0) => {
                        let containerWidth = row_inner.clientWidth;
                        let paneWidth = initialSize + offset;
                        let width = ((paneWidth / containerWidth) * 100).toFixed(1) + '%';
                        let gridColumns = this.$parent.$parent.row.settings.grid_template_columns.split(' ');

                        gridColumns[this.index] = width;

                        this.$parent.$parent.row.settings.grid_template_columns = gridColumns.join(' ');
                    };

                    // This adds is-resizing class to container
                    self.isResizing = true;

                    const onMouseMove = ({ pageX, pageY }) => {
                        resize(initialPaneWidth, pageX - initialPageX);
                    };

                    const onMouseUp = () => {
                        // Run resize one more time to set computed width/height.
                        resize(pane.clientWidth);

                        // This removes is-resizing class to container
                        self.isResizing = false;

                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                    };

                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                }
            }
        });

        Vue.component('builder-item', {
            props: {
                item: Object,
                items: Array,
                index: Number
            },
            template: `
            <div class="builder-item">
                    <div class="builder-item-actions">
                    <span @click="deleteItem" title="Delete item"><i class="fas fa-times"></i></span>
                </div>
                <div class="builder-item-inner" @click="editItem" :class="[ item.settings.is_hidden ? 'is-hidden' : '' ]">
                    <span class="item-drag" v-html="$root.layout_data[item.source]"></span>
                    <span v-if="item.settings.is_hidden"><i class="fas fa-eye-slash"></i></span>
                </div>
            </div>
            `,
            methods: {
                editItem() {
                    this.$root.$emit('edit-item', this.item);
                },
                deleteItem() {
                    this.items.splice(this.index, 1);
                    this.$root.$emit('edit-layout');
                }
            }
        });

        Vue.component('popover', {
            mixins: [builder_defaults],
            props: {
                col: Object
            },
            data() {
                return {
                    keywords: ''
                }
            },
            template: `
            <div class="popover">
                <div class="popover-search">
                    <input
                        type="text"
                        ref="keywords"
                        placeholder="Start typing"
                        v-model="keywords"
                    />
                </div>
                <div class="popover-choices">
                    <div
                        @click="saveItem(source)"
                        v-for="(label, source) in $root.layout_data"
                        v-show="isMatch(label)"
                        v-html="label">
                    </div>
                </div>
            </div>
            `,
            methods: {
                isMatch(label) {
                    let bool = ('' == this.keywords) ? true : false;

                    if (false === bool) {
                        let needle = this.keywords.toLowerCase();
                        let haystack = label.toLowerCase();
                        if (haystack.includes(needle)) {
                            bool = true;
                        }
                    }

                    return bool;
                },
                saveItem(source) {
                    if ('row' == source) {
                        let len = this.col.items.push(this.defaultRow());
                        this.$root.$emit('edit-row', this.col.items[len - 1], len);
                    }
                    else {
                        let len = this.col.items.push(this.defaultItem(source));
                        this.$root.$emit('edit-item', this.col.items[len - 1]);
                    }

                    this.$parent.adding_item = false;
                }
            },
            mounted() {
                this.$refs.keywords.focus();
            }
        });


        /* ================ facets / templates ================ */


        Vue.component('facets', {
            props: ['facets'],
            template: `
            <draggable class="facetwp-cards" v-model="$root.app.facets" handle=".card-drag">
                <div
                    class="facetwp-card"
                    v-for="(facet, index) in facets"
                    @click="$root.editItem('facet', facet)"
                >
                    <div class="card-drag">&#9776;</div>
                    <div class="card-label" :title="facet.name">{{ facet.label }}</div>
                    <div class="card-delete" @click.stop="$root.deleteItem('facet', index)"></div>
                    <div class="card-type">{{ facet.type }}</div>
                    <div class="card-source" v-html="getSource(facet.source)"></div>
                    <div class="card-rows">{{ getRowCount(facet.name) }}</div>
                </div>
            </draggable>
            `,
            methods: {
                getSource(source) {
                    return FWP.layout_data[source] || '-';
                },
                getRowCount(facet_name) {
                    if (this.$root.is_indexing) {
                        return '...';
                    }
                    return this.$root.row_counts[facet_name] || '-';
                }
            }
        });

        Vue.component('templates', {
            props: ['templates'],
            template: `
            <draggable class="facetwp-cards" v-model="$root.app.templates" handle=".card-drag">
                <div
                    class="facetwp-card"
                    v-for="(template, index) in templates"
                    @click="$root.editItem('template', template)"
                >
                    <div class="card-drag">&#9776;</div>
                    <div class="card-label" :title="template.name">{{ template.label }}</div>
                    <div class="card-delete" @click.stop="$root.deleteItem('template', index)"></div>
                    <div class="card-display-mode">{{ getDisplayMode(index) }}</div>
                    <div class="card-post-types">{{ getPostTypes(index) }}</div>
                </div>
            </draggable>
            `,
            methods: {
                getDisplayMode(index) {
                    let template = this.templates[index];
                    return ('undefined' !== typeof template.modes) ? template.modes.display : 'advanced';
                },
                getPostTypes(index) {
                    let template = this.templates[index];
                    if ('undefined' !== typeof template.modes) {
                        if ('visual' == template.modes.query) {
                            let post_types = template.query_obj.post_type;
                            if (0 === post_types.length) {
                                return '<any>';
                            }
                            else {
                                return post_types.map(type => type.label).join(', ');
                            }
                        }
                    }
                    return '<raw query>';
                }
            }
        });

        Vue.component('facet-edit', {
            data() {
                return {
                    facet: {}
                };
            },
            created() {
                this.facet = this.$root.editing;
            },
            template: `
            <div class="facetwp-content" :class="[ 'type-' + facet.type ]">
                <div class="facetwp-row">
                    <div>{{ 'Label' | i18n }}:</div>
                    <div>
                        <input
                            type="text"
                            v-model="facet.label"
                            @focus="$root.isNameEditable(facet)"
                            @keyup="$root.maybeEditName(facet)"
                        />
                        &nbsp; &nbsp; {{ 'Name' | i18n }}:
                        <input
                            type="text"
                            class="item-name"
                            v-model="facet.name"
                        />
                    </div>
                </div>
                <div class="facetwp-row">
                    <div>{{ 'Facet type' | i18n }}:</div>
                    <div>
                        <facet-types
                            :facet="facet"
                            :selected="facet.type"
                            :types="$root.facet_types">
                        </facet-types>
                        &nbsp; &nbsp;
                        <span class="facetwp-btn" @click="$root.copyToClipboard(facet.name, $event)">
                            {{ 'Copy shortcode' | i18n }}
                        </span>
                    </div>
                </div>
                <div class="facetwp-row field-data-source">
                    <div>{{ 'Data source' | i18n }}:</div>
                    <div>
                        <data-sources
                            :facet="facet"
                            :selected="facet.source"
                            :sources="$root.data_sources">
                        </data-sources>
                    </div>
                </div>
                <hr />
                <facet-settings :facet="facet"></facet-settings>
            </div>
            `
        });

        Vue.component('template-edit', {
            mixins: [builder_defaults],
            data() {
                return {
                    template: {},
                    tab: 'display'
                };
            },
            created() {
                this.template = this.$root.editing;

                // Set defaults for the layout builder
                if (! this.template.layout) {
                    Vue.set(this.template, 'layout', this.defaultLayout());
                }

                // Set defaults for the query builder
                if (! this.template.query_obj) {
                    Vue.set(this.template, 'query_obj', {
                        post_type: [],
                        posts_per_page: 10,
                        orderby: [],
                        filters: []
                    });
                }

                // Set the modes
                if (! this.template.modes) {
                    Vue.set(this.template, 'modes', {
                        display: ('' !== this.template.template) ? 'advanced' : 'visual',
                        query: ('' !== this.template.query) ? 'advanced' : 'visual'
                    });
                }
            },
            methods: {
                isMode(mode) {
                    return this.template.modes[this.tab] === mode;
                },
                switchMode() {
                    const now = this.template.modes[this.tab];
                    this.template.modes[this.tab] = ('visual' === now) ? 'advanced' : 'visual';
                }
            },
            template: `
            <div class="facetwp-content">
                <div class="table-row">
                    <input
                        type="text"
                        v-model="template.label"
                        @focus="$root.isNameEditable(template)"
                        @keyup="$root.maybeEditName(template)"
                    />
                    &nbsp; &nbsp; Name:
                    <input
                        type="text"
                        class="item-name"
                        v-model="template.name"
                    />
                </div>

                <div @click="switchMode()" v-show="isMode('visual')" class="side-link">{{ 'Switch to advanced mode' | i18n }}</div>
                <div @click="switchMode()" v-show="isMode('advanced')" class="side-link">{{ 'Switch to visual mode' | i18n }}</div>

                <div class="template-tabs top-level">
                    <span @click="tab = 'display'" :class="{ active: tab == 'display' }">{{ 'Display' | i18n }}</span>
                    <span @click="tab = 'query'" :class="{ active: tab == 'query' }">{{ 'Query' | i18n }}</span>
                </div>

                <div v-show="tab == 'display'">
                    <div class="table-row" v-show="template.modes.display == 'visual'">
                        <builder :layout="template.layout"></builder>
                    </div>
                    <div class="table-row" v-show="template.modes.display == 'advanced'">
                        <div class="side-link">
                            <a href="https://facetwp.com/documentation/templates/advanced-mode/" target="_blank">{{ 'Help' | i18n }}</a>
                        </div>
                        <div class="row-label">{{ 'Display Code' | i18n }}</div>
                        <textarea v-model="template.template"></textarea>
                    </div>
                </div>

                <div v-show="tab == 'query'">
                    <div class="table-row" v-show="template.modes.query == 'visual'">
                        <query-builder :query_obj="template.query_obj" :template="template"></query-builder>
                    </div>
                    <div class="table-row" v-show="template.modes.query == 'advanced'">
                        <div class="side-link">
                            <a href="https://facetwp.com/documentation/templates/advanced-mode/" target="_blank">{{ 'Help' | i18n }}</a>
                        </div>
                        <div class="row-label">{{ 'Query Arguments' | i18n }}</div>
                        <textarea v-model="template.query"></textarea>
                    </div>
                </div>
            </div>
            `
        });

        Vue.component('facet-types', {
            props: ['facet', 'selected', 'types'],
            template: `
            <select v-model="facet.type">
                <option v-for="(type, key) in types" :value="key" :selected="selected == key">{{ type.label }}</option>
            </select>
            `
        });

        Vue.component('facet-settings', {
            props: ['facet'],
            data() {
                return {
                    original_facet_type: ''
                };
            },
            template: `
            <div class="facet-fields">
                <component :is="dynComponent" v-bind="$props"></component>
            </div>
            `,
            computed: {

                tableToDivs() {
                    const self = this;
                    let html = this.$root.clone[this.facet.type];
                    let custom_settings = [];

                    // Backwards compatibility
                    html = html.replace(/<tr>/g, '<div class="facetwp-row">');
                    html = html.replace(/<td>/g, '<div class="facetwp-col">');
                    html = html.replace(/<\/td>/g, '</div>');
                    html = html.replace(/<\/tr>/g, '</div>');

                    // Remove default keys
                    const keys = Object.keys(this.facet).filter(key => {
                        return !['label', 'name', 'type', 'source', 'source_other'].includes(key);
                    });

                    // Add setting names by parsing the DOM
                    $(html).find('input, textarea, select').each(function() {
                        let $el = $(this);
                        let setting_name = $el.attr('class').split(' ')[0].replace(/-/g, '_').substr(6);
                        custom_settings.push(setting_name);

                        let is_new_type = (self.facet.type !== self.original_facet_type);
                        let is_new_setting = (!keys.includes(setting_name));

                        if (is_new_type || is_new_setting) {
                            let val = $el.val();

                            if ($el.is(':checkbox')) {
                                val = $el.is(':checked') ? 'yes' : 'no';
                            }
                            if ('[]' === val) {
                                val = [];
                            }

                            Vue.set(self.facet, setting_name, val);
                        }

                        if (is_new_setting) {
                            keys.push(setting_name);
                        }
                    });

                    keys.forEach((key) => {

                        // Delete orphan settings
                        if (!custom_settings.includes(key)) {
                            Vue.delete(this.facet, key);
                        }

                        // Dynamically add v-models
                        const key_dashed = key.replace(/_/g, '-');
                        const pattern = new RegExp('(class="facet-' + key_dashed + '")', 'gm');
                        const replacement = '$1 v-model="facet.' + key + '"';
                        html = html.replace(pattern, replacement);
                    });

                    return html;
                },

                // use a dynamic component so the data bindings (e.g. v-model) get compiled
                dynComponent() {
                    return {
                        template: '<div>' + this.tableToDivs + '</div>',
                        props: this.$options.props
                    }
                }
            },
            created() {
                this.original_facet_type = this.facet.type;
            },
            watch: {
                'facet.type': function(val) {
                    if ('search' == val) {
                        this.facet.source = '';
                    }
                },
                'facet.ghosts': function(val) {
                    if ('no' == val) {
                        this.facet.preserve_ghosts = 'no';
                    }
                },
                'facet.hierarchical': function(val) {
                    if ('no' == val) {
                        this.facet.show_expanded = 'no';
                    }
                }
            }
        });

        Vue.component('data-sources', {
            props: {
                facet: Object,
                selected: String,
                sources: Object,
                settingName: {
                    type: String,
                    default: 'source'
                }
            },
            template: `
            <select :id="rand" v-model="dataSourcesModel">
                <option v-if="settingName != 'source'" value="">{{ 'None' | i18n }}</option>
                <optgroup v-for="optgroup in sources" :label="optgroup.label">
                    <option v-for="(label, key) in optgroup.choices" :value="key" :selected="selected == key">{{ label }}</option>
                </optgroup>
            </select>
            `,
            methods: {
                fSelectChanged(event, $wrap) {

                    // only update this current instance
                    if (0 < $($wrap).find('#' + this.rand).length) {
                        this.facet[this.settingName] = this.$el.value;
                    }
                }
            },
            computed: {
                dataSourcesModel() {

                    // create the setting if needed
                    if ('undefined' === typeof this.facet[this.settingName]) {
                        Vue.set(this.facet, this.settingName, '');
                    }

                    return this.facet[this.settingName];
                }
            },
            created() {

                // create a random ID for each fSelect instance
                this.rand = 'fs-' + Math.random().toString(36).substring(7);
            },
            mounted() {
                $(this.$el).fSelect();
                $(document).on('fs:changed', this.fSelectChanged);
            },
            beforeDestroy() {
                $(document).off('fs:changed', this.fSelectChanged);
            }
        });

        // Vue instance
        FWP.vue = new Vue({
            el: '#app',
            data: {
                app: FWP.data,
                editing: {},
                editing_facet: false,
                editing_template: false,
                row_counts: {},
                facet_types: FWP.facet_types,
                data_sources: FWP.data_sources,
                layout_data: FWP.layout_data,
                query_data: FWP.query_data,
                support_html: FWP.support_html,
                clone: FWP.clone,
                active_tab: 'facets',
                active_subnav: 'general',
                is_support_loaded: false,
                is_name_editable: false,
                is_rebuild_open: false,
                is_indexing: false,
                timeout: null
            },
            methods: {
                addItem(type) {
                    if ('facet' == type) {
                        var index = this.app.facets.push({
                            'name': 'new_facet',
                            'label': 'New Facet',
                            'type': 'checkboxes',
                            'source': 'post_type'
                        });
                        this.editItem('facet', this.app.facets[index-1]);
                    }
                    else {
                        var index = this.app.templates.push({
                            'name': 'new_template',
                            'label': 'New Template',
                            'query': '',
                            'template': ''
                        });
                        this.editItem('template', this.app.templates[index-1]);
                    }
                },
                editItem(type, data) {
                    this['editing_' + type] = true;
                    this.editing = data;
                },
                doneEditing() {
                    this.editing_template = false;
                    this.editing_facet = false;
                    this.editing = {};
                },
                tabClick(which) {
                    this.doneEditing();
                    this.active_tab = which;
                    if ('support' === which) {
                        this.is_support_loaded = true;
                    }
                },
                getItemLabel() {
                    return this.editing.label;
                },
                deleteItem(type, index) {
                    if (confirm(FWP.__('Delete item?'))) {
                        this.app[type + 's'].splice(index, 1);
                    }
                },
                saveChanges() {
                    $('.facetwp-response').html(FWP.__('Saving') + '...');
                    $('.facetwp-response').addClass('visible');

                    // Settings save hook
                    const data = FWP.hooks.applyFilters('facetwp/save_settings', FWP.data);

                    $.ajax(ajaxurl, {
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'facetwp_save',
                            nonce: FWP.nonce,
                            data: JSON.stringify(data)
                        }
                    }).done(({message, reindex}) => {
                        $('.facetwp-response').html(message);
                        $('.facetwp-rebuild').toggleClass('flux', reindex);
                    }).fail(({status}, textStatus, errorThrown) => {
                        $('.facetwp-response').html(status + ' ' + errorThrown);
                    });
                },
                rebuildAction() {
                    this.is_indexing ? this.cancelReindex() : this.rebuildIndex();
                },
                rebuildIndex() {
                    let self = this;

                    $('.facetwp-rebuild').removeClass('flux');

                    if (this.is_indexing) {
                        return;
                    }

                    this.is_indexing = true;

                    $.post(ajaxurl, { action: 'facetwp_rebuild_index', nonce: FWP.nonce });
                    $('.facetwp-response').html(FWP.__('Indexing') + '... 0%');
                    $('.facetwp-response').addClass('visible');
                    this.timeout = setTimeout(() => {
                        self.getProgress();
                    }, 5000);
                },
                cancelReindex() {
                    let self = this;

                    $.ajax(ajaxurl, {
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'facetwp_get_info',
                            type: 'cancel_reindex',
                            nonce: FWP.nonce
                        }
                    }).done(({message}) => {
                        self.is_indexing = false;
                        clearTimeout(self.timeout);
                        $('.facetwp-response').html(message);
                    }).fail(({status}, textStatus, errorThrown) => {
                        $('.facetwp-response').html(status + ' ' + errorThrown);
                    });
                },
                getProgress() {
                    let self = this;

                    $.ajax(ajaxurl, {
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'facetwp_heartbeat',
                            nonce: FWP.nonce
                        }
                    }).done(function(data) {
                        if ('-1' == data.pct) {
                            $('.facetwp-response').html(FWP.__('Indexing complete'));
                            self.is_indexing = false;

                            // Update the row counts
                            $.each(data.rows, function(facet_name, count) {
                                Vue.set(self.row_counts, facet_name, count);
                            });
                        }
                        else if ($.isNumeric(data.pct)) {
                            $('.facetwp-response').html(FWP.__('Indexing') + '... ' + data.pct + '%');
                            $('.facetwp-response').addClass('visible');
                            self.is_indexing = true;

                            self.timeout = setTimeout(() => {
                                self.getProgress();
                            }, 5000);
                        }
                        else {
                            $('.facetwp-response').html(data);
                            self.is_indexing = false;
                        }
                    });
                },
                getInfo(type, label) {
                    $('.facetwp-response').html(FWP.__(label) + '...');
                    $('.facetwp-response').addClass('visible');

                    $.ajax(ajaxurl, {
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'facetwp_get_info',
                            type,
                            nonce: FWP.nonce
                        }
                    }).done(({message}) => {
                        $('.facetwp-response').html(message);
                    }).fail(({status}, textStatus, errorThrown) => {
                        $('.facetwp-response').html(status + ' ' + errorThrown);
                    });
                },
                getQueryArgs(template) {
                    let self = this;

                    template.modes.query = 'advanced';
                    template.query = FWP.__('Loading') + '...';

                    $.post(ajaxurl, {
                        action: 'facetwp_get_query_args',
                        query_obj: template.query_obj,
                        nonce: FWP.nonce
                    }, (message) => {
                        var json = JSON.stringify(message, null, 2);
                        json = "<?php\nreturn " + json + ';';
                        json = json.replace(/[\{]/g, '[');
                        json = json.replace(/[\}]/g, ']');
                        json = json.replace(/:/g, ' =>');
                        template.query = json;
                    }, 'json');
                },
                showIndexerStats() {
                    this.getInfo('indexer_stats', 'Looking');
                },
                searchablePostTypes() {
                    this.getInfo('post_types', 'Looking');
                },
                purgeIndexTable() {
                    this.getInfo('purge_index_table', 'Purging');
                },
                copyToClipboard(name, {target}) {
                    const $this = $(target);
                    const $el = $('.facetwp-clipboard');
                    const orig_text = $this.text();

                    try {
                        $el.removeClass('hidden');
                        $el.val('[facetwp facet="' + name + '"]');
                        $el.select();
                        document.execCommand('copy');
                        $el.addClass('hidden');
                        $this.text(FWP.__('Copied!'));
                    }
                    catch(err) {
                        $this.text(FWP.__('Press CTRL+C to copy'));
                    }

                    window.setTimeout(() => {
                        $this.text(orig_text);
                    }, 2000);
                },
                activate() {
                    $('.facetwp-activation-status').html(FWP.__('Activating') + '...');
                    $.post(ajaxurl, {
                        action: 'facetwp_license',
                        nonce: FWP.nonce,
                        license: $('.facetwp-license').val()
                    }, ({message}) => {
                        $('.facetwp-activation-status').html(message);
                    }, 'json');
                },
                isNameEditable({name}) {
                    this.is_name_editable = ('' == name || 'new_' == name.substr(0, 4));
                },
                maybeEditName(item) {
                    if (this.is_name_editable) {
                        let val = $.trim(item.label).toLowerCase();
                        val = val.replace(/[^\w- ]/g, ''); // strip invalid characters
                        val = val.replace(/[- ]/g, '_'); // replace space and hyphen with underscore
                        val = val.replace(/[_]{2,}/g, '_'); // strip consecutive underscores
                        item.name = val;
                    }
                },
                documentClick({target}) {
                    let el = target;

                    if (! el.classList.contains('btn-caret')) {
                        this.is_rebuild_open = false;
                    }
                }
            },
            computed: {
                isEditing() {
                    return this.editing_facet || this.editing_template;
                },
                indexButtonLabel() {
                    return this.is_indexing ? FWP.__('Stop indexer') : FWP.__('Re-index');
                }
            },
            created() {
                document.addEventListener('click', this.documentClick);
            },
            mounted() {
                this.getProgress();
            }
        });
    }

    function init_jquery() {

        // Export
        $(document).on('click', '.export-submit', () => {
                $('.import-code').val(FWP.__('Loading') + '...');
                $.post(ajaxurl, {
                    action: 'facetwp_backup',
                    nonce: FWP.nonce,
                    action_type: 'export',
                    items: $('.export-items').val()
                },
                response => {
                    $('.import-code').val(response);
                });
        });

        // Import
        $(document).on('click', '.import-submit', () => {
            $('.facetwp-response').addClass('visible');
            $('.facetwp-response').html(FWP.__('Importing') + '...');
            $.post(ajaxurl, {
                action: 'facetwp_backup',
                nonce: FWP.nonce,
                action_type: 'import',
                import_code: $('.import-code').val(),
                overwrite: $('.import-overwrite').is(':checked') ? 1 : 0
            },
            response => {
                $('.facetwp-response').html(response);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            });
        });

        // Tooltips
        $(document).on('mouseover', '.facetwp-tooltip', function() {
            if ('undefined' === typeof $(this).data('powertip')) {
                const content = $(this).find('.facetwp-tooltip-content').html();
                $(this).data('powertip', content);
                $(this).powerTip({
                    placement: 'e',
                    mouseOnToPopup: true
                });
                $.powerTip.show(this);
            }
        });

        // fSelect
        $('.export-items').fSelect();
    }

}))(jQuery);
