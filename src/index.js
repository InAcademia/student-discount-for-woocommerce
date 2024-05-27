/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { ExperimentalOrderMeta } from '@woocommerce/blocks-checkout';
import { useSelect } from '@wordpress/data';


function Stop(e) {
    e.preventDefault();
    e.stopPropagation();
}

function Button({url, coupon, img_validate, img_validated, excluded_product_ids}) {
	var button = "";

	const isButtonAllowed = useSelect( ( select ) => {
			return ! select( 'wc/store/cart' )
				.getCartData()
				.items.every( ( i )  => excluded_product_ids.includes(i.id))
	} );

	const isCouponPresent = useSelect( ( select ) => {
			return select( 'wc/store/cart' )
				.getCartData()
				.coupons.some( ( c ) => c.code === coupon );
		} );

	if (isButtonAllowed) {
		if (isCouponPresent) {
			button = (
				<a className="inacademia validated" href="#" onClick={Stop}>
				<img className="inacademia" src={img_validated} />&nbsp;
				<span className="">I'm a Student</span></a>
			)

		} else {
			button = (
				<>
					<a className="inacademia validate" href={url} target="_blank">
					<img className="inacademia" src={img_validate}/>&nbsp;
					<span className="">I'm a Student</span></a><br/>
					<i>Login at your university to apply a student discount</i><br/>
				</>
			)
		}
	}

	return (
		button
	)
}

const render = () => {
	const show_button = inacademia_data['button'];

	if (show_button === "on") {
		return (
			<ExperimentalOrderMeta>
				<div className="wc-block-components-totals-wrapper">
					<Button
						url={inacademia_data['url']}
						coupon={inacademia_data['coupon']}
						img_validate={inacademia_data['img_validate']}
						img_validated={inacademia_data['img_validated']}
						excluded_product_ids={inacademia_data['excluded_product_ids']}
					/>
				</div>
			</ExperimentalOrderMeta>
		);
	}
};

registerPlugin( 'inacademia', {
	render,
	scope: 'woocommerce-checkout',
} );
