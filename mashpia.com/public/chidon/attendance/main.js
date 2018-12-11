var users = {
    'wed' : [
        {
            id : 0,
            name : 'shimmy',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 1,
            name : 'naftoli',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 2,
            name : 'yossi',
            marked_by : '',
            marked_date : '',
            checked : 0
        }
    ],
    'thurs': [
        {
            id : 3,
            name : 'david',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 4,
            name : 'menachem',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 5,
            name : 'abba',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 6,
            name : 'rosie',
            marked_by : '',
            marked_date : '',
            checked : 0
        }
    ],
    'fri': [
        {
            id : 7,
            name : 'chaya',
            marked_by : '',
            marked_date : '',
            checked : 0
        },
        
        {
            id : 8,
            name : 'pinny',
            marked_by : '',
            marked_date : '',
            checked : 0
        }
    ]
};

Vue.component('user-list', {
    props: ['names'],
    
    template: `
        <ul>
            <div v-for="name in names">
                <user :name="name"></user>
            </div>
        </ul>
    `
});

Vue.component('user', {
    props: ['name'],
    
    template: `
        <label class="checkbox name">
            <div class="box">
                <input type="checkbox" :checked="name.checked" :id="name.id" :key="name.id" @click.prevent="change" />{{ name.name }} 
                <span style="float: right; width: 200px;">Marked On: <span style="font-size: 12px">{{ name.marked_date }}</span></span>
                <span style="float: right; font-style: italic; margin-right: 50px; width: 150px;">Marked By: {{ name.marked_by }}</span>
            </div>
        </label>
    `,
    
    methods: {
        change: function(e) {
            if (e.target.checked) {
                this.name.checked = 1;
                this.name.marked_by = 'shimmy';
                this.name.marked_date = new Date().toDateString();
            } else {
                this.name.checked = 0;
                this.name.marked_by = '';
                this.name.marked_date = '';
            }
        }
    }
});

new Vue({
    el: "#app",
    
    data: {
        header: "Attendance App",        
        names: users.wed,
    },
    
    created: function() {
        if (!sessionStorage.getItem('admin')) {
            location.href = "index.html";
        }
    },
    
    methods: {
        updateUsers: function(e) {
            this.names = users[e.target.value];
        }
    }
});