import bpy
import sys
import argparse

def render_thumbnail(input_path: str, output_path: str):
    bpy.ops.wm.read_factory_settings(use_empty=True)

    # Import .glb
    bpy.ops.import_scene.gltf(filepath=input_path)

    # Setup kamera
    bpy.ops.object.camera_add(location=(3, -3, 2))
    cam = bpy.context.object
    bpy.context.scene.camera = cam

    # Arahkan kamera ke center scene
    bpy.ops.object.select_all(action='SELECT')
    bpy.ops.view3d.camera_to_view_selected()

    # Setup lighting
    bpy.ops.object.light_add(type='SUN', location=(5, 5, 10))
    bpy.ops.object.light_add(type='AREA', location=(-3, -3, 4))

    # Render settings
    scene = bpy.context.scene
    scene.render.engine           = 'CYCLES'
    scene.render.resolution_x    = 400
    scene.render.resolution_y    = 300
    scene.render.image_settings.file_format = 'JPEG'
    scene.render.filepath         = output_path
    scene.cycles.samples          = 32

    bpy.ops.render.render(write_still=True)
    print(f"Thumbnail saved: {output_path}")

if __name__ == '__main__':
    argv = sys.argv
    argv = argv[argv.index('--') + 1:]

    parser = argparse.ArgumentParser()
    parser.add_argument('--input',  required=True)
    parser.add_argument('--output', required=True)
    args = parser.parse_args(argv)

    render_thumbnail(args.input, args.output)