<template>
  <div>
    <base-header class="pb-6">
      <b-row class="align-items-center py-4">
        <b-col lg="6" cols="7">
          <h6 class="h2 text-white d-inline-block mb-0">{{$route.name}}</h6>
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <route-breadcrumb/>
          </nav>
        </b-col>
       
      </b-row>
    </base-header>

    <div class="container-fluid mt--6">
      
        <b-col lg="6" >
          <div class="card-wrapper">
            <!-- Form controls -->
           
            <!-- HTML5 inputs -->
            <b-card no-body>
              <!-- Card header -->
              <b-card-header>
                <h3 class="mb-0">HTML5 inputs</h3>
              </b-card-header>
              <!-- Card body -->
              <b-card-body>
                <form>
                  <b-row class="form-group">
                    <label class="col-md-2 col-form-label form-control-label">Text</label>
                    <b-col md="10">
                      <base-input placeholder="Jon Snow"></base-input>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-search-input" class="col-md-2 col-form-label form-control-label">Search</label>
                    <b-col md="10">
                      <base-input id="example-search-input" placeholder="Tell me your secret ..."></base-input>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-email-input" class="col-md-2 col-form-label form-control-label">Email</label>
                    <b-col md="10">
                      <base-input type="email" autocomplete="username email" placeholder="argon@example.com" id="example-email-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-url-input" class="col-md-2 col-form-label form-control-label">URL</label>
                    <b-col md="10">
                      <base-input type="url" placeholder="https://www.creative-tim.com" id="example-url-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-tel-input" class="col-md-2 col-form-label form-control-label">Phone</label>
                    <b-col md="10">
                      <base-input type="tel" placeholder="40-(770)-888-444" id="example-tel-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-password-input"
                           class="col-md-2 col-form-label form-control-label">Password</label>
                    <b-col md="10">
                      <base-input type="password" autocomplete="current-password" placeholder="password" id="example-password-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-number-input" class="col-md-2 col-form-label form-control-label">Number</label>
                    <b-col md="10">
                      <base-input type="number" placeholder="23" id="example-number-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-datetime-local-input" class="col-md-2 col-form-label form-control-label">Datetime</label>
                    <b-col md="10">
                      <base-input type="datetime-local" value="2018-11-23T10:30:00" id="example-datetime-local-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-date-input" class="col-md-2 col-form-label form-control-label">Date</label>
                    <b-col md="10">
                      <base-input type="date" value="2018-11-23" id="example-date-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-month-input" class="col-md-2 col-form-label form-control-label">Month</label>
                    <b-col md="10">
                      <base-input type="month" value="2018-11" id="example-month-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-week-input" class="col-md-2 col-form-label form-control-label">Week</label>
                    <b-col md="10">
                      <base-input type="week" value="2018-W23" id="example-week-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-time-input" class="col-md-2 col-form-label form-control-label">Time</label>
                    <b-col md="10">
                      <base-input type="time" value="10:30:00" id="example-time-input"/>
                    </b-col>
                  </b-row>
                  <b-row class="form-group">
                    <label for="example-color-input" class="col-md-2 col-form-label form-control-label">Color</label>
                    <b-col md="10">
                      <base-input type="color" value="#5e72e4" id="example-color-input"/>
                    </b-col>
                  </b-row>
                </form>
              </b-card-body>
            </b-card>
          </div>
        </b-col>
   
    </div>
  </div>
</template>

<script>
  export default {
    name: 'form-elements',
    data() {
      return {
        inputs: {
          file: []
        },
        checkboxes: {
          unchecked: false,
          checked: true,
          uncheckedDisabled: false,
          checkedDisabled: true
        },
        radios: {
          radio1: 'checked',
          radio2: 'checked'
        }
      }
    },
    methods: {
      filesChange(files) {
        this.inputs.file = files
      }
    }
  }
</script>
