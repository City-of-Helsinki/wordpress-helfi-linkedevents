<table>
	<tbody>
		<?php foreach( $data as $filter => $config ) :
				$input_name = 'linked_events_config[' . $filter . '][]';
			?>
			<tr>
				<th><?php echo esc_html( $config['title'] ); ?></th>
				<td>
					<select name="<?php echo $input_name; ?>"<?php echo ! empty( $config['multiple'] ) ? ' multiple' : ''; ?>>
						<?php
							foreach ( $config['options'] as $id => $names ) {
								if ( is_object($names) ) {
									$name = $names->fi ?? $names->sv ?? $names->en ?? $id;
								} else if ( is_array( $names ) ) {
									$name = $names['fi'] ?? $names['sv'] ?? $names['en'] ?? $id;
								} else {
									$name = is_string( $names ) ? $names : $id;
								}

								printf(
									'<option value="%s" %s>%s</option>',
									$id ? esc_attr( $id ) : '',
									isset( $config['current'][$id] ) ? 'selected="selected"' : '',
									esc_html( $name )
								);
							}
						?>
					</select>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
