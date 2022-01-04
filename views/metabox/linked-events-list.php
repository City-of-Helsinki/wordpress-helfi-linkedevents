<table>
	<tbody>
		<tr>
			<th><?php esc_html_e( 'Events', 'helsinki-linkedevents' ); ?></th>
			<td>
				<?php if ( $data ) : ?>
					<ul>
						<?php foreach ( $data as $event ): ?>
							<li>
								<small>ID: <?php echo esc_html( $event->id() ); ?></small>
								<h2><?php echo esc_html( $event->name() ); ?></h2>
								<time><?php echo esc_html( $event->formatted_time_string() ); ?></time>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif;?>
			</td>
		</tr>
	</tbody>
</table>
