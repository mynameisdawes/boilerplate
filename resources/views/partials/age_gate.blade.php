<c-age_gate>
	<template v-slot:default="age_gate">
		<c-modal :trigger="(age_gate.age_verified == true ? false : true)" :required="true">
			<h3>Age gate</h3>
			<div class="field__collection flex">
				<c-input name="dob_day" v-model="age_gate.forms.age_gate.field_values.dob_day" placeholder="DD" :maxlength="2" type="number:maxlength" autotab autofocus></c-input>
				<c-input name="dob_month" v-model="age_gate.forms.age_gate.field_values.dob_month" placeholder="MM" :maxlength="2" type="number:maxlength" autotab></c-input>
				<c-input name="dob_year" v-model="age_gate.forms.age_gate.field_values.dob_year" placeholder="YYYY" :maxlength="4" type="number:maxlength" autotab></c-input>
			</div>
			<button @click="age_gate.checkAge" class="btn bg-primary border-primary text-primary_contrasting" :disabled="age_gate.age_entered == false">Enter</button>
		</c-modal>
	</template>
</c-age_gate>