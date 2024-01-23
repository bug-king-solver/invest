import DashboardLayout from '@/views/Layout/DashboardLayout.vue';
import AuthLayout from '@/views/Pages/AuthLayout.vue';
// GeneralViews
import NotFound from '@/views/GeneralViews/NotFoundPage.vue';

// Calendar

// Charts

// Components pages
const Buttons = () => import(/* webpackChunkName: "components" */ '@/views/Components/Buttons.vue');
const Cards = () => import(/* webpackChunkName: "components" */ '@/views/Components/Cards.vue');
const GridSystem = () => import(/* webpackChunkName: "components" */ '@/views/Components/GridSystem.vue');
const Notifications = () => import(/* webpackChunkName: "components" */ '@/views/Components/Notifications.vue');
const Icons = () => import(/* webpackChunkName: "components" */ '@/views/Components/Icons.vue');
const Typography = () => import(/* webpackChunkName: "components" */ '@/views/Components/Typography.vue');

// Dashboard pages
const Dashboard = () => import(/* webpackChunkName: "dashboard" */ '@/views/Dashboard/Dashboard.vue');
const AlternativeDashboard = () => import(/* webpackChunkName: "dashboard" */ '@/views/Dashboard/AlternativeDashboard.vue');


// Forms pages
const FormElements = () => import(/* webpackChunkName: "forms" */ '@/views/Forms/FormElements.vue');
const FormComponents = () => import(/* webpackChunkName: "forms" */ '@/views/Forms/FormComponents.vue');
const FormValidation = () => import(/* webpackChunkName: "forms" */ '@/views/Forms/FormValidation.vue');

// Maps pages


// Pages
const User = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/UserProfile.vue');
const Pricing = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/Pricing.vue');
const TimeLine = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/TimeLinePage.vue');
const Login = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/Login.vue');
const Home = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/Home.vue');
const Register = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/Register.vue');
const Lock = () => import(/* webpackChunkName: "pages" */ '@/views/Pages/Lock.vue');

// TableList pages

const CompanyTables = () => import(/* webpackChunkName: "tables" */ '@/views/Tables/CompanyTables.vue');
const UsersTables = () => import(/* webpackChunkName: "tables" */ '@/views/Tables/UsersTables.vue');
let componentsMenu = {
  path: '/components',
  component: DashboardLayout,
  redirect: '/components/buttons',
  name: 'Components',
  children: [
    {
      path: 'buttons',
      name: 'Buttons',
      component: Buttons
    },
    {
      path: 'cards',
      name: 'Cards',
      component: Cards
    },
    {
      path: 'grid-system',
      name: 'Grid System',
      component: GridSystem
    },
    {
      path: 'notifications',
      name: 'Notifications',
      component: Notifications
    },
    {
      path: 'icons',
      name: 'Icons',
      component: Icons
    },
    {
      path: 'typography',
      name: 'Typography',
      component: Typography
    }
  ]
};
let formsMenu = {
  path: '/forms',
  component: DashboardLayout,
  redirect: '/forms/elements',
  name: 'Forms',
  children: [
    {
      path: 'elements',
      name: 'Form elements',
      component:  FormElements
    },
    {
      path: 'components',
      name: 'Form components',
      component:  FormComponents
    },
    {
      path: 'validation',
      name: 'Form validation',
      component:  FormValidation
    }
  ]
};

let tablesMenu = {
  path: '/tables',
  component: DashboardLayout,
  redirect: '/table/regular',
  name: 'Tables menu',
  children: [
    
    {
      path: 'company',
      name: 'Company Tables',
      component: CompanyTables
    },
    {
      path: 'users',
      name: 'Users Tables',
      component: UsersTables
    }
  ]
};



let pagesMenu = {
  path: '/pages',
  component: DashboardLayout,
  name: 'Pages',
  redirect: '/pages/user',
  children: [
    {
      path: 'user',
      name: 'User Page',
      component: User
    },
    {
      path: 'timeline',
      name: 'Timeline Page',
      component: TimeLine
    }
  ]
};

let authPages = {
  path: '/',
  component: AuthLayout,
  name: 'Authentication',
  children: [
    {
      path: '/home',
      name: 'Home',
      component: Home,
      meta: {
        noBodyBackground: true
      }
    },
    {
      path: '/login',
      name: 'Login',
      component: Login
    },
    {
      path: '/register',
      name: 'Register',
      component: Register
    },
    {
      path: '/pricing',
      name: 'Pricing',
      component: Pricing
    },
    {
      path: '/lock',
      name: 'Lock',
      component: Lock
    },
    { path: '*', component: NotFound }
  ]
};

const routes = [
  {
    path: '/',
    redirect: '/home',
    name: 'Home'
  },
  componentsMenu,
  formsMenu,
  tablesMenu,
  pagesMenu,
  {
    path: '/',
    component: DashboardLayout,
    redirect: '/dashboard',
    name: 'Dashboard layout',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'alternative',
        name: 'Alternative',
        component: AlternativeDashboard,
        meta: {
          navbarType: 'light'
        }
      },
     
      
      
    ]
  },
  authPages,
];

export default routes;
